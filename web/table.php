
<?php
// Initialize the session - is required to check the login state.
session_start();
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['google_loggedin'])) {
    header('Location: login.php');
    exit;
}
// Retrieve session variables
$google_loggedin = $_SESSION['google_loggedin'];
$google_email = $_SESSION['google_email'];
$google_name = $_SESSION['google_name'];
$google_picture = $_SESSION['google_picture'];
// ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Kasatria Data Visualization</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<link type="text/css" rel="stylesheet" href="main.css">
        <link rel="icon" href="logo.jpg" type="image/jpg">
        <script type="importmap">
            {
              "imports": {
                "three": "https://cdn.jsdelivr.net/npm/three@0.165.0/build/three.module.js",
                "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.165.0/examples/jsm/"
              }
            }
          </script>
		<style>
			a {
				color: #8ff;
			}
			.center {
				display: flex;
				margin: auto;
				transform: translateX(80%);
			}
			#menu {
				position: absolute;
        		display:flex;
				bottom: 20px;
				width: 100%;
				text-align: right;
				align-items: center;
			}

			.element {
				width: 120px;
				height: 180px;
				font-family: Helvetica, sans-serif;
				text-align: center;
				line-height: normal;
				cursor: default;
				position: relative;
			}

			.element:hover {
				box-shadow: 0px 0px 12px rgba(0,255,255,0.75);
				border: 1px solid rgba(127,255,255,0.75);
			}

				.element .number {
					display: flex;
  					justify-content: space-between;
					font-size: 12px;
					color: white;
					margin-left: 10px;
  					margin-right: 10px;
				}

				.element .symbol {
					font-weight: bold;
					color: white;
					text-shadow: 0 0 10px rgba(0,255,255,0.95);
					width: 100px;
					height: 100px;
				}

				.element .details {
					position: absolute;
					bottom: 15px;
					left: 0px;
					right: 0px;
					font-size: 9px;
					color: white;
				}

			button {
				color: rgba(127,255,255,0.75);
				background: transparent;
				outline: 1px solid rgba(127,255,255,0.75);
				border: 0px;
				padding: 5px 10px;
				cursor: pointer;
			}

			button:hover {
				background-color: rgba(0,255,255,0.5);
			}

			button:active {
				color: #000000;
				background-color: rgba(0,255,255,0.75);
			}
			.bar-container {
				display: flex;
				gap: 10px;
				align-items: center;
				margin-right: auto;
			}
			.bar {
				background: rgb(239,48,34);
				background: linear-gradient(90deg, rgba(239,48,34,1) 0%, rgba(253,202,53,1) 47%, rgba(58,159,72,1) 100%);
				width:250px;
				height:20px;
			}
		</style>
	</head>
	<body>

		<div id="info"> Welcome,  <?php echo $google_name ?> <a href="logout.php" class="logout-btn"> Logout</a> <a href="doc.html" class="logout-btn" target="_blank">Documentation</a></div>
		
		<div id="container"></div>
		<div id="menu">
			<div class="center">
				<button id="table">TABLE</button>
				<button id="sphere">SPHERE</button>
				<button id="helix">DOUBLE HELIX</button>
				<button id="grid">GRID</button>
			</div>
			<div class="bar-container"><p>LOW</p><div class="bar"></div><p>High</p></div>
		</div>

		<script type="importmap">
			{
				"imports": {
					"three": "../build/three.module.js",
					"three/addons/": "./jsm/"
				}
			}
		</script>

		<script type="module">

			import * as THREE from 'three';

			import TWEEN from 'three/addons/libs/tween.module.js';
			import { TrackballControls } from 'three/addons/controls/TrackballControls.js';
			import { CSS3DRenderer, CSS3DObject } from 'three/addons/renderers/CSS3DRenderer.js';

			//import sheet data
			const sheetId = "1xfam0WX2zMEXRHUzSMXUGEl6YooHmaN_XWb8M-saErs";
			// sheetName is the name of the TAB in your spreadsheet
			const sheetName = encodeURIComponent("Sheet1");
			const sheetURL = `https://docs.google.com/spreadsheets/d/${sheetId}/gviz/tq?tqx=out:csv&sheet=${sheetName}`;

			

			function handleResponse(csvText) {
				const table = csvToObjects(csvText);
				return table;

			}
			function csvToObjects(csv) {
				const csvRows = csv.split("\n");
				const propertyNames = csvSplit(csvRows[0]);
				let objects = [];
				for (let i = 1, max = csvRows.length; i < max; i++) {
					let thisObject = {};
					let row = csvSplit(csvRows[i]);
					for (let j = 0, max = row.length; j < max; j++) {
					thisObject[propertyNames[j]] = row[j];
					}
					objects.push(thisObject);
				}
				return objects;
				}
				function csvSplit(row) {
					let insideQuotes = false;
					let currentValue = '';
					let values = [];

					for (let char of row) {
						if (char === '"') {
						insideQuotes = !insideQuotes;
						} else if (char === ',' && !insideQuotes) {
						values.push(currentValue.trim());
						currentValue = '';
						} else {
						currentValue += char;
						}
					}
					values.push(currentValue.trim()); 

					// Remove surrounding quotes from each value if they exist
					return values.map(value => {
						if (value.startsWith('"') && value.endsWith('"')) {
						return value.slice(1, -1);
						}
						return value;
					});
				}
					
			fetch(sheetURL).then((response) => response.text()).then((csvText) => {
				const table = handleResponse(csvText);
				let camera, scene, renderer;
			let controls;

			const objects = [];
			const targets = { table: [], sphere: [], helix: [], grid: [] };

			init();
			animate();

			function init() {

				camera = new THREE.PerspectiveCamera( 40, window.innerWidth / window.innerHeight, 1, 10000 );
				camera.position.z = 3000;

				scene = new THREE.Scene();
				// table

				for ( let i = 0; i < table.length; i ++ ) {
					

					const element = document.createElement( 'div' );
					element.className = 'element';

					if(table[i]["Net Worth"].replace("$","").replace(",","") > 200000) {
						element.style.border = '1px solid #3A9F48';
						element.style.boxShadow = '0px 0px 12px #3A9F48';
						element.style.backgroundColor = 'rgba(2,38,7,100)'
					}
					else if(table[i]["Net Worth"].replace("$","").replace(",","") > 100000) {
						element.style.border = '1px solid #FDCA35';
						element.style.boxShadow = '0px 0px 12px #FDCA35';
						element.style.backgroundColor = 'rgba(40,30,3,100)'
					}
					else if (table[i]["Net Worth"].replace("$","").replace(",","") < 100000) {
						element.style.border = '1px solid #EF3022';
						element.style.boxShadow = '0px 0px 12px #EF3022';
						element.style.backgroundColor = 'rgba(33,3,1,100)'
					}

					const number = document.createElement('div');
					number.className = 'number';
					const country = document.createElement('p');
					country.innerHTML = table[i].Country;
					const age = document.createElement('p');
					age.innerHTML = table[i].Age;
					element.appendChild(number);
					number.appendChild(country);
					number.appendChild(age);



					const symbol = document.createElement( 'img' );
					symbol.className = 'symbol';
					symbol.src =  table[i].Photo;
					element.appendChild( symbol );

					const details = document.createElement( 'div' );
					details.className = 'details';
					details.innerHTML = '<b>' + table[i].Name + '</b>' + '<br>' + table[i].Interest;
					element.appendChild( details );

					const objectCSS = new CSS3DObject( element );
					objectCSS.position.x = Math.random() * 4000 - 2000;
					objectCSS.position.y = Math.random() * 4000 - 2000;
					objectCSS.position.z = Math.random() * 4000 - 2000;
					scene.add( objectCSS );

					objects.push( objectCSS );

					const object = new THREE.Object3D();
					const columns = 20;
					const spacing = 200;
					object.position.x = (i % columns) * spacing - ((columns - 1) * spacing) / 2;
					object.position.y = Math.floor(i / columns) * spacing - ((Math.ceil(table.length / columns) - 1) * spacing) / 2;
					object.position.z = -800;
					
					targets.table.push( object );

				}

				// sphere

				const vector = new THREE.Vector3();

				for ( let i = 0, l = objects.length; i < l; i ++ ) {

					const phi = Math.acos( - 1 + ( 2 * i ) / l );
					const theta = Math.sqrt( l * Math.PI ) * phi;

					const object = new THREE.Object3D();

					object.position.setFromSphericalCoords( 800, phi, theta );

					vector.copy( object.position ).multiplyScalar( 2 );

					object.lookAt( vector );

					targets.sphere.push( object );

				}

				// helix

				for ( let i = 0, l = objects.length; i < l; i ++ ) {
				
					// Create double helix
					const radius = 800;
					const spacing = 80;
					const heightOffset = 1000;
					const phaseShift = Math.PI; // 180 degrees

					const theta1 = i * 0.175 + Math.PI;
					const theta2 = theta1 + phaseShift;

					let y;
					if (i % 2 === 0) {
						y = -(Math.floor(i / 2) * spacing) + heightOffset; // First helix row
					} else {
						y = -(Math.floor(i / 2) * spacing) + heightOffset; // Second helix row
					}

					const object1 = new THREE.Object3D();
					object1.position.setFromCylindricalCoords(radius, theta1, y);
					object1.lookAt(new THREE.Vector3(object1.position.x * 2, object1.position.y, object1.position.z * 2));
					targets.helix.push(object1);

					const object2 = new THREE.Object3D();
					object2.position.setFromCylindricalCoords(radius, theta2, y);
					object2.lookAt(new THREE.Vector3(object2.position.x * 2, object2.position.y, object2.position.z * 2));
					targets.helix.push(object2);

				}

				// grid
				const columns = 5;
				const rows = 4;
				const layers = 10;
				const spacingX = 300;
				const spacingY = 300;
				const spacingZ = 300;
				for ( let i = 0; i < objects.length; i ++ ) {

					const object = new THREE.Object3D();

					const layer = Math.floor(i / (columns * rows)); // Calculate current layer
					const row = Math.floor((i % (columns * rows)) / columns); // Calculate current row within layer
					const column = i % columns; // Calculate current column within row

					object.position.x = (column * spacingX) - ((columns - 1) * spacingX / 2);
					object.position.y = (-row * spacingY) + ((rows - 1) * spacingY / 2);
					object.position.z = (layer * spacingZ) - ((layers - 1) * spacingZ / 2);

					targets.grid.push(object);

				}

				//

				renderer = new CSS3DRenderer();
				renderer.setSize( window.innerWidth, window.innerHeight );
				document.getElementById( 'container' ).appendChild( renderer.domElement );

				//

				controls = new TrackballControls( camera, renderer.domElement );
				controls.minDistance = 500;
				controls.maxDistance = 6000;
				controls.addEventListener( 'change', render );

				const buttonTable = document.getElementById( 'table' );
				buttonTable.addEventListener( 'click', function () {

					transform( targets.table, 2000 );

				} );

				const buttonSphere = document.getElementById( 'sphere' );
				buttonSphere.addEventListener( 'click', function () {

					transform( targets.sphere, 2000 );

				} );

				const buttonHelix = document.getElementById( 'helix' );
				buttonHelix.addEventListener( 'click', function () {

					transform( targets.helix, 2000 );

				} );

				const buttonGrid = document.getElementById( 'grid' );
				buttonGrid.addEventListener( 'click', function () {

					transform( targets.grid, 2000 );

				} );

				transform( targets.table, 2000 );

				//

				window.addEventListener( 'resize', onWindowResize );

			}

			function transform( targets, duration ) {

				TWEEN.removeAll();

				for ( let i = 0; i < objects.length; i ++ ) {

					const object = objects[ i ];
					const target = targets[ i ];

					new TWEEN.Tween( object.position )
						.to( { x: target.position.x, y: target.position.y, z: target.position.z }, Math.random() * duration + duration )
						.easing( TWEEN.Easing.Exponential.InOut )
						.start();

					new TWEEN.Tween( object.rotation )
						.to( { x: target.rotation.x, y: target.rotation.y, z: target.rotation.z }, Math.random() * duration + duration )
						.easing( TWEEN.Easing.Exponential.InOut )
						.start();

				}

				new TWEEN.Tween( this )
					.to( {}, duration * 2 )
					.onUpdate( render )
					.start();

			}

			function onWindowResize() {

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

				render();

			}

			function animate() {

				requestAnimationFrame( animate );

				TWEEN.update();

				controls.update();

			}

			function render() {

				renderer.render( scene, camera );

			}

			});
			
		</script>
	</body>
</html>