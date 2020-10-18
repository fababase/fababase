<?php

namespace App\Http\Controllers\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;;
use App\Http\Controllers\Controller;

class FieldTrialDataController extends Controller
{

	/**
	 * Downloads selected formula outputs
	 */
	public function download(Request $request)
	{
		$results = $this->getResultsByFormula($request);

		$callback = function() use ($results) 
		{
			$file = fopen('php://output', 'w');

			// Column names
			fputcsv($file, array_keys((array)$results[0]));

			foreach ($results as $row) {
				fputcsv($file, (array)$row);
			}

			fclose($file);
		};

		$headers = [
			'Content-Type' => 'text/csv',
		];
		$filename = $request->input('formula');
		return response()->streamDownload($callback, "$filename.csv", $headers);
	}

	/**
	 * Downloads the entire field trial dataset
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	public function downloadAll(Request $request)
	{
		$fs = Storage::getDriver();
		$stream = $fs->readStream('field-trial-data--norfab.tar.gz');

		return response()->stream(
			function() use($stream) {
				fpassthru($stream);
			}, 
			200,
			[
				'Content-Type' => Storage::mimeType('field-trial-data--norfab.tar.gz'),
				'Content-disposition' => 'attachment; filename="field-trial-data--norfab.tar.gz"',
			]);
	}

	/**
	 * Returns allowed distinct values for a given whitelisted unqiue column
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	public function getAllowedValues(Request $request) {
    $column = $request->input('column');

		$whitelist = [
			'PDID',
			'TRID',
		];

		try {
			if (!in_array($column, $whitelist)) {
				throw new \Exception("The column provided, $column, is invalid");
			}

			switch ($column) {
				case 'PDID':
					return DB::table($this->getPrefixedTableName($request, 'PD'))->orderByRaw("LENGTH($column)", 'ASC')->orderBy($column)->groupBy($column)->pluck($column);
				case 'TRID':
					return DB::table($this->getPrefixedTableName($request, 'TR'))->orderByRaw("LENGTH($column)", 'ASC')->orderBy($column)->groupBy($column)->pluck($column);
				default:
					throw new \Exception("Unreachable case error: $column");
			}
		} catch (\Exception $e) {
			return response()->json([
				'message' => $e->getMessage(),
			], 400);
		} 
	}

	public function searchByColumn(Request $request) {
		$column = $request->input('column');
		$keyword = $request->input('keyword');

		$whitelist = [
			'PDID',
			'TRID',
		];

		try {
			if (!in_array($column, $whitelist)) {
				throw new \Exception("The column provided, $column, is invalid");
			}

			switch ($column) {
				case 'PDID':
					return DB::table($this->getPrefixedTableName($request, 'PD'))
						->select('PDID', 'DescriptionOfTrait', 'Grouping', 'DescriptionOfMethod', 'Comments')
						->where('PDID', 'LIKE', $keyword.'%')
						->orWhere('DescriptionOfTrait', 'LIKE', '%'.$keyword.'%')
						->orWhere('Grouping', 'LIKE', '%'.$keyword.'%')
						->orWhere('DescriptionOfMethod', 'LIKE', '%'.$keyword.'%')
						->orWhere('Comments', 'LIKE', '%'.$keyword.'%')
						->get();
				case 'TRID':
					return DB::table($this->getPrefixedTableName($request, 'TR'))
						->select('TRID', 'PlotSize', 'SoilType', 'StartOfTrial', 'EndOfTrial', 'Description', 'Manager', 'Comments')
						->where('TRID', 'LIKE', $keyword.'%')
						->orWhere('PlotSize', 'LIKE', '%'.$keyword.'%')
						->orWhere('SoilType', 'LIKE', '%'.$keyword.'%')
						->orWhere('StartOfTrial', 'LIKE', '%'.$keyword.'%')
						->orWhere('EndOfTrial', 'LIKE', '%'.$keyword.'%')
						->orWhere('Description', 'LIKE', '%'.$keyword.'%')
						->orWhere('Manager', 'LIKE', '%'.$keyword.'%')
						->orWhere('Comments', 'LIKE', '%'.$keyword.'%')
						->get();
				default:
					throw new \Exception("Unreachable case error: $column");
			}
		} catch (\Exception $e) {
			return response()->json([
				'message' => $e->getMessage(),
			], 400);
		} 
	}

	/**
	 * @return	DBObject	A DB object for a given whitelisted formula
	 */
	private function getResultsByFormula(Request $request) {
		switch ($request->input('formula')) {
			case 'get-all-trial-data-by-phenotype':
				return $this->getTrialDataByPhenotype($request);
			case 'get-all-phenotypes-scored-by-trial':
				return $this->getPhenotypesScoredByTrial($request);
			case 'get-phenotype-data-by-trial-and-trait':
				return $this->getPhenotypeDataByTrialAndTrait($request);
		}
	}

	private function getTrialDataByPhenotype(Request $request) {
    $tablePD = $this->getPrefixedTableName($request, 'PD');
    $tablePH = $this->getPrefixedTableName($request, 'PH');
    $tablePL = $this->getPrefixedTableName($request, 'PL');
    $tableTR = $this->getPrefixedTableName($request, 'TR');

		return DB::table($this->getPrefixedTableName($request, 'PD'))
			->select(
				$tablePH.'.PHID',
				$tablePH.'.Date',
				$tablePH.'.Score',
				$tablePH.'.ScoredBy',
        $tablePH.'.Comments',
        
				$tablePD.'.PDID',
				$tablePD.'.DescriptionOfTrait',
				$tablePD.'.Grouping',
				$tablePD.'.DescriptionOfMethod',
        $tablePD.'.Comments',
        
				$tableTR.'.TRID'
			)
			->leftJoin($tablePH, $tablePD.'.PDID', '=', $tablePH.'.PDID')
			->leftJoin($tablePL, $tablePH.'.PLID', '=', $tablePL.'.PLID')
			->leftJoin($tableTR, $tablePL.'.TRID', '=', $tableTR.'.TRID')
			->where([
				[$tablePH.'.PDID', '=', $request->input('PDID')],
			])
			->get(); 
	}

	private function getPhenotypesScoredByTrial(Request $request) {
    $tablePD = $this->getPrefixedTableName($request, 'PD');
    $tablePH = $this->getPrefixedTableName($request, 'PH');
    $tablePL = $this->getPrefixedTableName($request, 'PL');
    $tableTR = $this->getPrefixedTableName($request, 'TR');

		return DB::table($tablePD)
			->select(
				$tablePH.'.PHID',
				$tablePH.'.Date',
				$tablePH.'.Score',
				$tablePH.'.ScoredBy',
        $tablePH.'.Comments',
        
				$tablePD.'.PDID',
				$tablePD.'.DescriptionOfTrait',
				$tablePD.'.Grouping',
				$tablePD.'.DescriptionOfMethod',
        $tablePD.'.Comments',
        
				$tableTR.'.TRID'
			)
			->leftJoin($tablePH, $tablePD.'.PDID', '=', $tablePH.'.PDID')
			->leftJoin($tablePL, $tablePH.'.PLID', '=', $tablePL.'.PLID')
			->leftJoin($tableTR, $tablePL.'.TRID', '=', $tableTR.'.TRID')
			->where([
				[$tableTR.'.TRID', '=', $request->input('TRID')],
			])
			->get();
	}

	private function getPhenotypeDataByTrialAndTrait(Request $request) {
    $tableGP = $this->getPrefixedTableName($request, 'GP');
    $tablePD = $this->getPrefixedTableName($request, 'PD');
    $tablePH = $this->getPrefixedTableName($request, 'PH');
    $tablePL = $this->getPrefixedTableName($request, 'PL');
    $tableSL = $this->getPrefixedTableName($request, 'SL');
    $tableTR = $this->getPrefixedTableName($request, 'TR');

		return DB::table($tablePH)
			->select(
				$tablePH.'.PHID',
				$tablePH.'.SLID',
				$tablePH.'.PDID',
				$tablePH.'.Score',
				$tablePH.'.ScoredBy',
				$tablePH.'.Date',
				$tablePH.'.Comments',
				
				$tableGP.'.Description',
				$tablePD.'.DescriptionOfMethod',
				$tablePL.'.GPSCoordinates',
				$tablePL.'.PLID',
				$tableTR.'.PlotSize'
			)
			->leftJoin($tablePL, $tablePH.'.PLID', '=', $tablePL.'.PLID')
			->leftJoin($tableTR, $tablePL.'.TRID', '=', $tableTR.'.TRID')
			->leftJoin($tableGP, $tableSL.'.GPID', '=', $tableGP.'.GPID')
			->leftJoin($tablePD, $tablePD.'.PDID', '=', $tablePH.'.PDID')
			->where([
				[$tablePH.'.PDID', '=', $request->input('PDID')],
				[$tableTR.'.TRID', '=', $request->input('TRID')],
			])
			->get();
  }
  
  private function getPrefixedTableName(Request $request, string $tableName)
  {
    $user = $request->user();
    if (!$user->can('read field trial data')) {
      throw new \Exception("Insufficient privilege to access field trial data");
    }

    if ($user->hasRole('norfabUser')) {
      return 'Nor'.$tableName;
    }
    else if ($user->hasRole('profabaUser')) {
      return 'Profaba'.$tableName;
    } else {
      throw new \Exception("User role has no known columns mapped");
    }
  }
}
