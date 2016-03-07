/**
 * ポリラインのデコード処理
 * Refer to https://developers.google.com/maps/documentation/utilities/polylinealgorithm?hl=ja
 * @param {String} path
 * @return {Array}
 */
function decodePath(path) {
	var results = [];
	var coords = { latitude: null, longitude: null };
	var nextValueFlag = false;
	var num = "";
	var numArray = [];
	for (var i = 0, len = path.length; i < len; i++) {
		// ASCII文字を2進数の値に変換
		numArray[i] = (path[i].charCodeAt(0) - 63).toString(2);
		// 後続のビット集合が続くかの判断
		// 0x20との論理和演算が行われていたかを確認する
		if (numArray[i].length >= 6) {
			// 後続のビット集合が続く場合用の処理をデコードする
			numArray[i] = (parseInt(numArray[i], 2) - 32).toString(2);
		} else {
			// 後続なしなので、数値の切れ目となる
			nextValueFlag = true;
		}
		// 5ビットの集合に戻す
		// 逆順に並び替えていたのを戻す
		// 右側から5ビットの集合に分割していたのを戻す - numに代入していく
		while (numArray[i].length < 5) {
			numArray[i] = "0" + numArray[i];
		}
		num = numArray[i] + num;
		// 数値の切れ目の場合は結果にデコードした値を格納する
		if (nextValueFlag) {
			nextValueFlag = false;
			// 符号の判断
			// 負の値の場合はエンコード時左に1ビットシフト後に反転しているので奇数になっている
			if (parseInt(num, 2) % 2 === 0) {
				// 2進数を10進数に変換
				num = (parseInt(num, 2) / 2 / 1e5);
			} else {
				// 2進数を10進数に変換 - 負の値用の処理
				num = (~(parseInt(num, 2)) / 2 / 1e5);
			}
			// 緯度経度をまとめて結果に格納するための処理
			if (coords.latitude === null) {
				coords.latitude = num;
			} else if (coords.longitude === null) {
				coords.longitude = num;
			}
			if (coords.latitude !== null && coords.longitude !== null) {
				// ２地点目以降は前の地点からのオフセットの値となっているので、緯度経度を求める
				if (results.length > 0) {
					coords.latitude = results[results.length - 1].latitude + coords.latitude;
					coords.longitude = results[results.length - 1].longitude + coords.longitude;
				}
				// 5桁に丸める
				coords.latitude = parseFloat(coords.latitude.toFixed(5));
				coords.longitude = parseFloat(coords.longitude.toFixed(5));
				// 結果を格納
				results.push(coords);
				// 緯度経度リセット
				coords = { latitude: null, longitude: null };
			}
			// 次の数値を求めるために、一時保持していた値をリセット
			num = "";
		}
	}
	return results;
}
