<?php
/*
 * SF利用累計額、ポイント累積、付与チケット、チケット累計
 * 1000,1000,100,100
 * 2000,2000,100,200
 * 3000,3000,160,360
 * 4000,4000,160,520
 * 5000,5000,330,850
 * 6000,6000,170,1020
 * 7000,7000,180,1200
 * 8000,8000,180,1380
 * 9000,9000,180,1560
 * 10000,10000,180,1740
 * 
 * ① 乗車時にＳＦでお支払いいただいた運賃と同額の「バスポイント」をＰＡＳＭＯに付与します。
 * ②  1,000 ポイントごとに「特典バスチケット」をＰＡＳＭＯに付与します。
 * ③ バスチケットは、次回のバス乗車時に、自動的に運賃として使用されます。
 * ④ バスポイントは、10,000 ポイントごとに繰り返されます。
 * ⑤ 利用金額の累計は、１ヶ月間（毎月１日から末日）でリセットされます。
 * ⑥ バスチケットの有効期限は、１０年間です。
 * ⑦ 川崎病院線（ワンコインバス）では、バス特は適用されません。
 */

/**
 * 交通費計算のスクリプト
 * 
 *	 使い方(料金が206円の例)
 *	 	料金表を表示:
 *	 		$ php bus_fee.php
 *	 	出力結果
 *	 		1: 412
 *	 		2: 824
 *	 		  : 
 *	 		10: 3760
 *	 		  : 
 *	 		20: 7040
 *	 		  : 
 *	 
 *	 	月前半の交通費を表示: 例) 10日間
 *	 		$ php bus_fee.php 10
 *	 	出力結果
 *	 		10: 3760
 *	 
 *	 	月後半の交通費を表示: 例) 前半10日間、後半10日間の場合
 *	 		$ php bus_fee.php 10 10
 *	 	出力結果
 *	 		10(20): 3280(7040 - 3760)
 * 
 * @param $argv[1] (optional) 月前半の日数
 * @param $argv[2] (optional) 月後半の日数
 */

// コンフィグ
$fee = 206;
$day = 31;
$ticket_array = array(
	100, // 1000
	100, // 2000
	160, // 3000
	160, // 4000
	330, // 5000
	170, // 6000
	180, // 7000
	180, // 8000
	180, // 9000
	180  // 10000
);

// 計算の準備
$fee_sum = 0;
$count = $day * 2;
$latest_ticket = 0;
$current_ticket;
$current_ticket_index;
$ticket;
$discount_flag = false;

// 計算
$fee_array = array();
for ($i = 1; $i <= $count; $i++) {
	// 料金の合計
	$fee_sum += $fee;
	if ($discount_flag === true) {
		// 付与チケットを利用して料金を割引きする
		$discount_flag = false;
		$fee_sum = $fee_sum - $ticket;
	}

	// 特典バスチケットの付与チケットを計算する
	$ticket = 0;
	// 1000円毎の累計金額を超えているかを見る
	$current_ticket = (floor($fee_sum / 1000) - 1);
	if ($current_ticket > -1) {
		if ($current_ticket > $latest_ticket) {
			$latest_ticket = $current_ticket;
			// 10000円毎のループに対応できるように一桁目の数字を利用する
			$current_ticket_index = $current_ticket % (pow(10, 1));
			$ticket = $ticket_array[$current_ticket_index];

			// 付与チケットは次回乗車時に利用可能なのでディスカウントフラグをtrueにしておく
			$discount_flag = true;
		}
	} else {
		// 1000未満の料金なのでチケットが付与されない場合はココにくる
		$latest_ticket = $current_ticket;
	}

	// 配列に格納、料金の表示
	if ($i % 2 == 0) {
		// 計算結果の格納
		$fee_array[$i / 2] = $fee_sum;

		// 「日にち: 料金」の表示(引数がなかったら料金表を表示)
		if ( ! isset($argv[1])) {
			echo ($i / 2) . ": " . $fee_sum;
			echo "\n";
		}
	}
}

// 交通費計算
if (isset($argv[1])) {
	$argv_first = $argv[1];
	$fee_first = $fee_array[$argv_first];

	if ( ! isset($argv[2])) {
		// 引数で日数を受け取り、その日数の料金を表示する
		echo "$argv_first: " . $fee_first;
	} else {
		// 月の後半の場合は差額を計算する（15日締め対応）
		$argv_second = $argv[2];
		$day_sum = $argv_first + $argv_second;
		$fee_sum = $fee_array[$day_sum];
		$fee_second = $fee_sum - $fee_first;
		echo "$argv_second($day_sum): " . $fee_second . "($fee_sum - $fee_first)";
	}
	echo "\n";
}

?>
