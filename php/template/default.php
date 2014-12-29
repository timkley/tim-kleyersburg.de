<? 
	ob_start();
?>
		<html>

			<head>
		
			</head>
		
			<body>
			
				<div><? echo $message; ?></div>
			
			</body>
			
		</html>
<?
	$contents=ob_get_contents();
   ob_end_clean();
   return($contents);
?>


	