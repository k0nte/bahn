<?php
$maps = ["https://direkt.bahn.guru/?origin=$id", 
		"https://www.chronotrains.com/de/station/$id-/6",
		"https://travic.app/?z=12&x=$x&y=$y&l=osm_standard&ol=orm_infra"];
		
$iframe = $maps;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahnu Karten</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
			width: 100%;
            overflow: hidden; /* prevent scrollbars */
            display: flex;
            flex-direction: row;
        }
        #container {
            flex: 1; /* Allow container to grow */
            display: flex;
            flex-direction: row;
            position: relative;
			width: 100%;
        }
        iframe {
            border: none;
            height: 100%;
			float: left;
			min-width: 10px;
        }
        #leftFrame {
            width: 50%;
        }
        #rightFrame {
            flex-grow: 1; /* Allow right frame to grow */
        }
        #dragBar {
            width: 10px;
            background-color: #ddd;
            cursor: ew-resize;
			position: relative;
			z-index: 4;
        }
		#over {
			position: fixed;
			width: calc(100% - 80px);
			height: 100%;
			overflow: hidden;
			z-index: 2;
			background: #1111;
			display: none;
			padding: 40px;
			font-size: 1.6em;
		}
		#over a {
			background: #eee;
			padding: 3px
		}
    </style>
</head>
<body>
<div id="over">
<a href="<?php echo $iframe[1] ?>" style="float: right"><?php echo $iframe[1] ?></a>
<a href="<?php echo $iframe[0] ?>"><?php echo $iframe[0] ?></a><br><br>
Auch gut:<br>
<a href="https://travic.app/?ol=orm_infra">travic.app: Live-Bus-und-Bahn-Positionen</a>
</div>
<div id="container">
    <iframe id="leftFrame" src="<?php echo $iframe[0] ?>"></iframe>
	<div id="dragBar"></div>
	<iframe id="rightFrame" src="<?php echo $iframe[1] ?>"></iframe>
</div>

<script>
    const dragBar = document.getElementById('dragBar');
    const container = document.getElementById('container');
    const leftFrame = document.getElementById('leftFrame');
	const over = document.getElementById('over')
    let isDragging = false;
	let wasDragging = false;
	
	function disableSelect(event) {     event.preventDefault(); }
	window.addEventListener('selectstart', disableSelect);
	
    dragBar.addEventListener('mousedown', function (e) {
        isDragging = true;
		over.style.display = 'block';
    });

    document.addEventListener('mousemove', function (e) {
		console.log("H")
        if (!isDragging) return;
		wasDragging = true;

        const mouseX = e.clientX;
        const containerRect = container.getBoundingClientRect();
        const totalWidth = containerRect.width;
        const dragBarWidth = dragBar.offsetWidth;

        const containerX = containerRect.left;

        let newWidth = (mouseX - containerX - dragBarWidth / 2) / totalWidth * 100;
        newWidth = Math.min(Math.max(newWidth, 5), 95); // Ensure dragBar stays within container

        leftFrame.style.width = newWidth + '%';
    });

    document.addEventListener('mouseup', function (e) {
        isDragging = false;
		if (!wasDragging)
			over.style.display = "block";
		else
			over.style.display = "none";
		wasDragging = !wasDragging
    });
</script>

</body>
</html>
