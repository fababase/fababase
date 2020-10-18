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
