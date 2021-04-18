/** @method */
export function intersection(arr1, arr2) {
	return arr1.filter(x => arr2.includes(x));
}

/** @method */
export function isIntersecting(arr1, arr2) {
	return !!intersection(arr1, arr2).length;
}

/** @method */
export function removeEntryByValue(arr, valueToRemove) {
	return arr.filter(value => {
		return value !== valueToRemove;
	});
}

/** @method */
export function pickNonUniqueItems(arr, minCount) {
	const counts = { };
	if (minCount === void 0) {
		minCount = 2;
	}
	
	arr.forEach(i => {
		if (i in counts) {
			counts[i]++;
		} else {
			counts[i] = 1;
		}
	});
	
	const nonUniqueItems = [];
	for (const key in counts) {
		const count = counts[key];
		if (count >= minCount) {
			nonUniqueItems.push(key);
		}
	}
	
	return nonUniqueItems;
}

/** @method */
export function textualJoin(arr, separator, finalSeparator) {
	// Defaults
	separator = separator || ',';
	finalSeparator = finalSeparator || 'and';

	const arrCopy = [...arr];

	// Shortcircuit if array has a single item
	if (arrCopy.length === 1) {
		return arrCopy[0];
	}

	const lastItem = arrCopy.pop();

	// NOTE: Use oxford comma if array length > 2
	return arrCopy.join(separator + ' ') + (arr.length > 2 ? `${separator} ` : ' ') + finalSeparator + ' ' + lastItem;
}
