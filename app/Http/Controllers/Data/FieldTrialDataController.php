<?php

namespace App\Http\Controllers\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class FieldTrialDataController extends Controller
{

	/**
	 * Downloads selected formula outputs
	 */
	public function download(Request $request)
	{
		$project = strtolower($request->input('project'));
    if (!in_array($project, ['norfab', 'profaba'])) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}
		
		if ($request->input('isfile') === '1') {
			return $this->downloadStaticFile($request);
		} else {
			$results = $this->getResultsByFormula($request);
		}

		if (!count($results)) {
			$referer = request()->headers->get('referer');
			$cookie = Cookie::make('fababase_error', 'No rows returned');
			return redirect()->away($referer)->withCookie($cookie);
		}

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
	 * Downloads the entire field trial dataset sans genotype data
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	private function downloadStaticFile(Request $request) {
		switch ($request->input('formula')) {
			case 'get-genotypes-without-mapping-data':
				return $this->downloadGenotypingData($request);
			case 'get-genotypes-by-map-name':
				return $this->downloadGenotypingDataByMapName($request);
		}
	}

	/**
	 * Downloads the entire field trial dataset sans genotype data
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	public function downloadAll(Request $request)
	{
    $project = strtolower($request->input('project'));
    if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}
		
		return $this->downloadFileByFileName('field-trial-data--'.$project.'.tar.gz');
	}

	/**
	 * Gets the filesize of the full dataset without genotype data
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	public function getDownloadAllFileSize(Request $request)
	{
		$project = strtolower($request->input('project'));
    if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}
		
		return $this->getFileSizeByFileName('field-trial-data--'.$project.'.tar.gz');
	}

	/**
	 * Downloads the entire genotype dataset
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	public function downloadRawGenotype(Request $request)
	{
    $project = strtolower($request->input('project'));
    if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}
		
		return $this->downloadFileByFileName('field-trial-data--raw-genotype--'.$project.'.xlsx.gz');
	}

	/**
	 * Gets the filesize of the genotype dataset
	 *
	 * @param	\Illuminate\Http\Request $request
	 * @return	\Illuminate\Http\Response
	 */
	public function getDownloadGenotypeFileSize(Request $request)
	{
		$project = strtolower($request->input('project'));
    if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}

		return $this->getFileSizeByFileName('field-trial-data--raw-genotype--'.$project.'.xlsx.gz');
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
			'MapName',
			'PDID',
			'TRID'
		];

		try {
			if (!in_array($column, $whitelist)) {
				throw new \Exception("The column provided, $column, is invalid");
			}

			switch ($column) {
				case 'MapName':
					return DB::table($this->getPrefixedTableName($request, 'MP'))->orderByRaw("LENGTH($column)", 'ASC')->orderBy($column)->groupBy($column)->pluck($column);
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
			'MapName',
			'PDID',
			'TRID',
		];

		try {
			if (!in_array($column, $whitelist)) {
				throw new \Exception("The column provided, $column, is invalid");
			}

			switch ($column) {
				case 'MapName':
					return DB::table($this->getPrefixedTableName($request, 'MP'))
						->select('MPID', 'SNID', 'MapName', 'Chromosome', 'Position', 'Comments')
						->where('MapName', 'LIKE', $keyword.'%')
						->orWhereRaw("MATCH(Comments) AGAINST(?)", [$keyword])
						->groupBy('MapName')
						->get();
				case 'PDID':
					return DB::table($this->getPrefixedTableName($request, 'PD'))
						->select('PDID', 'DescriptionOfTrait', 'Grouping', 'DescriptionOfMethod', 'Comments')
						->where('PDID', 'LIKE', $keyword.'%')
						->orWhereRaw("MATCH(DescriptionOfTrait) AGAINST(?)", [$keyword])
						->orWhereRaw("MATCH(Grouping) AGAINST(?)", [$keyword])
						->orWhereRaw("MATCH(DescriptionOfMethod) AGAINST(?)", [$keyword])
						->orWhereRaw("MATCH(Comments) AGAINST(?)", [$keyword])
						->get();
				case 'TRID':
					$query = DB::table($this->getPrefixedTableName($request, 'TR'))
						->select('TRID', 'PlotSize', 'SoilType');

					// ProFaba contains additional columns
					if ($request->input('project') === 'profaba') {
						$query->addSelect('SoilTexture', 'SoilDepth', 'Distance');
					}

					$query->addSelect('StartOfTrial', 'EndOfTrial', 'Description', 'Manager', 'Comments');

					$query	
						->where('TRID', 'LIKE', $keyword.'%')
						->orWhere('PlotSize', 'LIKE', '%'.$keyword.'%')
						->orWhere('SoilType', 'LIKE', '%'.$keyword.'%')
						->orWhere('StartOfTrial', 'LIKE', '%'.$keyword.'%')
						->orWhere('EndOfTrial', 'LIKE', '%'.$keyword.'%')
						->orWhereRaw("MATCH(Description) AGAINST(?)", [$keyword])
						->orWhereRaw("MATCH(Manager) AGAINST(?)", [$keyword])
						->orWhereRaw("MATCH(Comments) AGAINST(?)", [$keyword]);

					return $query->get();
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
    $tableGP = $this->getPrefixedTableName($request, 'GP');
    $tablePD = $this->getPrefixedTableName($request, 'PD');
    $tablePH = $this->getPrefixedTableName($request, 'PH');
    $tablePL = $this->getPrefixedTableName($request, 'PL');
    $tableTR = $this->getPrefixedTableName($request, 'TR');
    $tableSL = $this->getPrefixedTableName($request, 'SL');

		$query = DB::table($this->getPrefixedTableName($request, 'PD'))
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

        $tableSL.'.SLID',
        $tableSL.'.HarvestDate',
        $tableSL.'.HarvestLocation',
        $tableSL.'.ParentSLID',
        $tableSL.'.Comments',

        $tableGP.'.GPID',
        $tableGP.'.Name',
        $tableGP.'.AlternativeName',
        $tableGP.'.Donor',
        $tableGP.'.GeographicOrigin',
        $tableGP.'.Maintaining',
        $tableGP.'.Comments'
			);

		// ProFaba contains additional columns for TR table
		$project = $request->input('project');
		$query->addSelect(
			$tableTR.'.TRID',
			$tableTR.'.GPSCoordinates',
			$tableTR.'.PlotSize',
			$tableTR.'.PlotSizeIncludingpaths',
			$tableTR.'.SoilType'
		);
		if ($project === 'profab') {
			$query->addSelect(
				$tableTR.'.SoilTexture',
				$tableTR.'.SoilDepth',
				$tableTR.'.Distance'
			);
		}
		$query->addSelect(
			$tableTR.'.StartOfTrial',
			$tableTR.'.EndOfTrial',
			$tableTR.'.Description',
			$tableTR.'.Manager',
			$tableTR.'.Comments'
		);

		$query
			->leftJoin($tablePH, $tablePD.'.PDID', '=', $tablePH.'.PDID')
			->leftJoin($tablePL, $tablePH.'.PLID', '=', $tablePL.'.PLID')
      ->leftJoin($tableTR, $tablePL.'.TRID', '=', $tableTR.'.TRID')
      ->leftJoin($tableSL, $tablePL.'.SLID', '=', $tableSL.'.SLID')
      ->leftJoin($tableGP, $tableSL.'.GPID', '=', $tableGP.'.GPID')
			->where([
				[$tablePH.'.PDID', '=', $request->input('PDID')],
			]);

		return $query->get();
	}

	private function getPhenotypesScoredByTrial(Request $request) {
    $tableGP = $this->getPrefixedTableName($request, 'GP');
    $tablePD = $this->getPrefixedTableName($request, 'PD');
    $tablePH = $this->getPrefixedTableName($request, 'PH');
    $tablePL = $this->getPrefixedTableName($request, 'PL');
    $tableSL = $this->getPrefixedTableName($request, 'SL');
    $tableTR = $this->getPrefixedTableName($request, 'TR');

		$query = DB::table($tablePD)
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

        $tableSL.'.SLID',
        $tableSL.'.HarvestDate',
        $tableSL.'.HarvestLocation',
        $tableSL.'.ParentSLID',
        $tableSL.'.Comments',

        $tableGP.'.GPID',
        $tableGP.'.Name',
        $tableGP.'.AlternativeName',
        $tableGP.'.Donor',
        $tableGP.'.GeographicOrigin',
        $tableGP.'.Maintaining',
        $tableGP.'.Comments'
			);

		// ProFaba contains additional columns for TR table
		$project = $request->input('project');
		$query->addSelect(
			$tableTR.'.TRID',
			$tableTR.'.GPSCoordinates',
			$tableTR.'.PlotSize',
			$tableTR.'.PlotSizeIncludingpaths',
			$tableTR.'.SoilType'
		);
		if ($project === 'profab') {
			$query->addSelect(
				$tableTR.'.SoilTexture',
				$tableTR.'.SoilDepth',
				$tableTR.'.Distance'
			);
		}
		$query->addSelect(
			$tableTR.'.StartOfTrial',
			$tableTR.'.EndOfTrial',
			$tableTR.'.Description',
			$tableTR.'.Manager',
			$tableTR.'.Comments'
		);

		$query
			->leftJoin($tablePH, $tablePD.'.PDID', '=', $tablePH.'.PDID')
			->leftJoin($tablePL, $tablePH.'.PLID', '=', $tablePL.'.PLID')
      ->leftJoin($tableTR, $tablePL.'.TRID', '=', $tableTR.'.TRID')
      ->leftJoin($tableSL, $tablePL.'.SLID', '=', $tableSL.'.SLID')
      ->leftJoin($tableGP, $tableSL.'.GPID', '=', $tableGP.'.GPID')
			->where([
				[$tableTR.'.TRID', '=', $request->input('TRID')],
			]);
		
		return $query->get();
	}

	private function getPhenotypeDataByTrialAndTrait(Request $request) {
    $tableGP = $this->getPrefixedTableName($request, 'GP');
    $tablePD = $this->getPrefixedTableName($request, 'PD');
    $tablePH = $this->getPrefixedTableName($request, 'PH');
    $tablePL = $this->getPrefixedTableName($request, 'PL');
    $tableSL = $this->getPrefixedTableName($request, 'SL');
    $tableTR = $this->getPrefixedTableName($request, 'TR');

		$query = DB::table($tablePD)
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

        $tableSL.'.SLID',
        $tableSL.'.HarvestDate',
        $tableSL.'.HarvestLocation',
        $tableSL.'.ParentSLID',
        $tableSL.'.Comments',

        $tableGP.'.GPID',
        $tableGP.'.Name',
        $tableGP.'.AlternativeName',
        $tableGP.'.Donor',
        $tableGP.'.GeographicOrigin',
        $tableGP.'.Maintaining',
        $tableGP.'.Comments'
			);

		// ProFaba contains additional columns for TR table
		$project = $request->input('project');
		$query->addSelect(
			$tableTR.'.TRID',
			$tableTR.'.GPSCoordinates',
			$tableTR.'.PlotSize',
			$tableTR.'.PlotSizeIncludingpaths',
			$tableTR.'.SoilType'
		);
		if ($project === 'profab') {
			$query->addSelect(
				$tableTR.'.SoilTexture',
				$tableTR.'.SoilDepth',
				$tableTR.'.Distance'
			);
		}
		$query->addSelect(
			$tableTR.'.StartOfTrial',
			$tableTR.'.EndOfTrial',
			$tableTR.'.Description',
			$tableTR.'.Manager',
			$tableTR.'.Comments'
		);

		$query
			->leftJoin($tablePH, $tablePD.'.PDID', '=', $tablePH.'.PDID')
			->leftJoin($tablePL, $tablePH.'.PLID', '=', $tablePL.'.PLID')
      ->leftJoin($tableTR, $tablePL.'.TRID', '=', $tableTR.'.TRID')
      ->leftJoin($tableSL, $tablePL.'.SLID', '=', $tableSL.'.SLID')
      ->leftJoin($tableGP, $tableSL.'.GPID', '=', $tableGP.'.GPID')
			->where([
				[$tablePD.'.PDID', '=', $request->input('PDID')],
				[$tableTR.'.TRID', '=', $request->input('TRID')],
			]);
		
		return $query->get();
	}

	private function downloadGenotypingData(Request $request)
	{
		$project = strtolower($request->input('project'));
    if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}

		return $this->downloadFileByFileName('field-trial-genotype-data--'.$project.'.tsv.gz');
	}

	private function downloadGenotypingDataByMapName(Request $request)
	{
		$project = strtolower($request->input('project'));
    if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		}

		return $this->downloadFileByFileName('field-trial-genotype-data-by-map-name--'.$request->input('MapName').'--'.$project.'.tsv.gz');
	}
	
  private function getPrefixedTableName(Request $request, string $tableName)
  {
    $user = $request->user();
		$project = $request->input('project');
		if (!$this->isProjectValid($project)) {
      return response()->json([
				'message' => 'Project does not exist',
			], 404);
		} else  if (!$user->can('read field trial data')) {
      throw new \Exception("Insufficient privilege to access field trial data");
    }

    if ($user->hasRole('norfabUser') && $project == 'norfab') {
      return 'Nor'.$tableName;
    } else if ($user->hasRole('profabaUser') && $project == 'profaba') {
      return 'Pro'.$tableName;
    } else {
      throw new \Exception("User role has no known columns mapped");
    }
	}

	private function downloadFileByFileName(string $filename)
	{
		$fs = Storage::getDriver();
		$stream = $fs->readStream($filename);

		return response()->stream(
			function() use($stream) {
				fpassthru($stream);
			}, 
			200,
			[
				'Content-Type' => Storage::mimeType($filename),
				'Content-disposition' => 'attachment; filename="'.$filename.'"',
			]);
	}
	
	private function getFileSizeByFileName(string $filename)
	{
		try {
			return response()->json([
				'fileSize' => Storage::size($filename),
			]);
		} catch (\Exception $e) {
			return response()->json([
				'fileSize' => 0,
			]);
		}
	}

	private function isProjectValid(string $project)
	{
    return in_array($project, ['norfab', 'profaba']);
	}
}
