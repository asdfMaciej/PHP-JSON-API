<?php
function getContrastColor($hexColor) {

        //////////// hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        //////////// Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

         //////////// Calc contrast ratio
         $L1 = 0.2126 * pow($R1 / 255, 2.2) +
               0.7152 * pow($G1 / 255, 2.2) +
               0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
              0.7152 * pow($G2BlackColor / 255, 2.2) +
              0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        //////////// If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return 'black';
        } else { //////////// if not, return white color.
            return 'white';
        }
}
?>
<?php if (isset($schedule_who)): ?>
<h1> Plan lekcji - <?=$schedule_who?> </h2>
<?php endif ?>
<?php
$days_timetable = 0; 
foreach ($days as $dzien) {
	if ($dzien) {
		$days_timetable += 1;
	}
}
?>
<table class="schedule">
		<tr class="godziny">
			<?php if ($days_timetable > 1): ?> 
			<th>
				
			</th>
			<?php endif ?>
			<td>
				<b>1</b>
				<br>
				7:45 - 8:30
			</td>
			<td>
				<b>2</b>
				<br>
				8:40 - 9:25
			</td>
			<td>
				<b>3</b>
				<br>
				9:35 - 10:20
			</td>
			<td>
				<b>4</b>
				<br>
				10:30 - 11:15
			</td>
			<td>
				<b>5</b>
				<br>
				11:25 - 12:10
			</td>
			<td>
				<b>6</b>
				<br>
				12:20 - 13:05
			</td>
			<td>
				<b>7</b>
				<br>
				13:25 - 14:10
			</td>
			<td>
				<b>8</b>
				<br>
				14:30 - 15:15
			</td>
		</tr>
		<?php
		foreach ($days as $dzien_n => $dzien):
			if (!$dzien) {continue;}
		?>
		<tr>
			<?php if ($days_timetable > 1): ?>
			<th>
				<?=$dzien?>
			</th>
			<?php endif ?>
			<?php foreach (["1","2","3","4","5","6","7","8"] as $lekcja): ?>
				<?php if (isset($schedule[$dzien_n+1][$lekcja])): ?>
					<?php $l = $schedule[$dzien_n+1][$lekcja]; $padding = count($l) > 1 ? 'padding: 0;' : '';?>
					<td style="background-color: <?=$l[0]["subject_color"]?>; <?=$padding?>" title="<?=$l[0]["class"]?>">
						<?php foreach ($l as $l_unit): ?>
						<a href="/schedule/teacher/<?=$l_unit["teacher_id"]?>" style="text-decoration: none; color: <?=getContrastColor($l[0]["subject_color"])?>">
							<?php if (count($l) > 1): ?>
							<div style="width: 100%">
								<?=$l_unit["subject"]?> <br>
								<u><?=$l_unit["classroom"]?></u> <br>
								<span style="font-size: 80%"><?=$l_unit["teacher"]?></span>
							</div>
							<?php else: ?>
							<?=$l_unit["subject"]?> <br>
							<u><?=$l_unit["classroom"]?></u> <br>
							<span style="font-size: 80%"><?=$l_unit["teacher"]?></span>
							<?php endif?>
						</a>
						<?php endforeach ?>
					</td>
				<?php else: ?>
					<td style="border: 0;">

					</td>
				<?php endif ?>
			<?php endforeach ?>
			
		</tr>
		<?php endforeach ?>
		
	</table>