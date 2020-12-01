	  document.addEventListener("touchstart", touchHandler, true);
	  document.addEventListener("touchmove", touchHandler, true);
	  document.addEventListener("touchend", touchHandler, true);
	  document.addEventListener("touchcancel", touchHandler, true);
	  var rotateStartPoint = new THREE.Vector3(0, 0, 1);
	  var rotateEndPoint = new THREE.Vector3(0, 0, 1);
	  var deltaX = 0,
	      deltaY = 0;
	  var startPoint = {
	      x: 0,
	      y: 0
	  };

	  var windowHalfX = window.innerWidth / 2;
	  var windowHalfY = window.innerHeight / 2;

	  var isLoaded = false;
	  var rotationSpeed = 10;
	  var mesh;
	  window.initScene = function(file) {
	      var myCanvas = document.getElementById('stlCanvas');
	      if (!Detector.webgl) {
	          Detector.addGetWebGLMessage();
	          document.getElementById('viewer').innerHTML = document.getElementById('oldie').innerHTML;
	          document.getElementById('oldie').style.visibility = 'hidden';
	          return;
	      } else {
	          renderer = new THREE.WebGLRenderer({
	              antialias: true,
	              alpha: true,
	              canvas: myCanvas
	          });
	          renderer.setSize(myCanvas.offsetWidth, myCanvas.offsetHeight);
	          scene = new THREE.Scene();
	          var dirLight = new THREE.DirectionalLight(0xffffff, 1);
	          dirLight.position.set(1, 1, 1);

	          var loader = new THREE.STLLoader();

	          loader.load(file, function(geometry) {
	              geometry.computeBoundingSphere();
	              geometry.computeVertexNormals();

	              var r = geometry.boundingSphere.radius;
	              geometry.computeBoundingBox();
	              geometry.center();

	              if (geometry.hasColors) {
	                  material = new THREE.MeshPhongMaterial({
	                      opacity: geometry.alpha,
	                      vertexColors: THREE.VertexColors
	                  });
	              } else {
	                  var material = new THREE.MeshLambertMaterial({
	                      color: 0x999999,
	                      side: THREE.DoubleSide
	                  });
	              }
	              mesh = new THREE.Mesh(geometry, material);
	              mesh.position.set(0, 0, 0.0);
	              mesh.rotation.set(-Math.PI / 3, 0, 0);
	              mesh.castShadow = true;
	              mesh.receiveShadow = true;
	              scene.add(mesh);

	              var aspectRatio = myCanvas.offsetWidth / myCanvas.offsetHeight;
	              var fov = 75;
	              camera = new THREE.PerspectiveCamera(fov, aspectRatio, 1, 1000);
	              camera.up = new THREE.Vector3(0, 1, 0);
	              camera.lookAt(new THREE.Vector3(0, 0, 0));
	              var hFov = toDegree(2 * Math.atan(Math.tan(toRad(fov / 2)) * aspectRatio));
	              var distance = Math.max(findDistance(fov, geometry.boundingBox.max.y), findDistance(hFov, geometry.boundingBox.max.x));
	              camera.position.z = distance + geometry.boundingBox.max.z;
	              camera.add(dirLight);
	              scene.add(camera);
	              var bbox = new THREE.BoundingBoxHelper(mesh);
	              bbox.update();
	              this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
	              this.controls.target = new THREE.Vector3(0, 0, 0);
	              this.controls.maxDistance = 3000;
	              this.controls.enableRotate = false;
	              isLoaded = true;
	              document.addEventListener('mousedown', onDocumentMouseDown, true);
	              animate();
	          });

	          function findDistance(fov, r) {
	              return r / Math.tan(toRad(fov / 2));
	          }

	          function toRad(degree) {
	              return Math.PI * 2 * degree / 360;
	          }

	          function toDegree(rad) {
	              return 360 * rad / (2 * Math.PI);
	          }
	      }
	  }

	  function clamp(x, min, max) {

	      return Math.min(max, Math.max(min, x));
	  }

	  /**
	   * Most of the code from: http://projects.defmech.com/ThreeJSObjectRotationWithQuaternion/
	   * http://projects.defmech.com/ThreeJSObjectRotationWithQuaternion/Rotation.js
	   * http://stackoverflow.com/questions/23223431/how-to-rotate-object-in-three-jsr66-not-use-trackball-which-is-control-the-cam
	   * https://github.com/defmech/Three.js-Object-Rotation-with-Quaternion
	   */
	  function projectOnTrackball(touchX, touchY) {
	      var mouseOnBall = new THREE.Vector3();

	      var meshScreenPos = toScreenPosition(mesh, camera, true);

	      var minWinSize = Math.min(windowHalfX, windowHalfY);

	      mouseOnBall.set(
	          clamp(touchX / minWinSize, -1, 1), clamp(-touchY / minWinSize, -1, 1),
	          0.0
	      );

	      var length = mouseOnBall.length();

	      if (length > 1.0) {
	          mouseOnBall.normalize();
	      } else {
	          mouseOnBall.z = Math.sqrt(1.0 - length * length);
	      }

	      return mouseOnBall;
	  }

	  // http://stackoverflow.com/questions/27409074/three-js-converting-3d-position-to-2d-screen-position-r69 
	  // Did not work as intended...
	  function toScreenPosition(obj, camera, ndc) {
	      var vector = new THREE.Vector3();

	      var widthHalf = 0.5 * renderer.context.canvas.width;
	      var heightHalf = 0.5 * renderer.context.canvas.height;

	      obj.updateMatrixWorld();
	      vector.setFromMatrixPosition(obj.matrixWorld);
	      vector.project(camera);

	      if (ndc) {

	          return new THREE.Vector2().set(vector.x, vector.y);
	      }

	      vector.x = (vector.x * widthHalf) + widthHalf;
	      vector.y = -(vector.y * heightHalf) + heightHalf;

	      return new THREE.Vector2().set(vector.x, vector.y);

	  };

	  function findQuaternion(start, end) {
	      var axis = new THREE.Vector3(),
	          quaternion = new THREE.Quaternion();

	      var angle = Math.acos(start.dot(end) / start.length() / end.length());

	      if (angle) {
	          axis.crossVectors(start, end).normalize();
	          angle *= rotationSpeed;
	          quaternion.setFromAxisAngle(axis, angle);
	      }

	      return quaternion;
	  }

	  function onRotate() {
	      if (!isLoaded) return;

	      rotateEndPoint = projectOnTrackball(deltaX, deltaY);
	      deltaX = 0;
	      deltaY = 0;

	      var rotateQuaternion = findQuaternion(rotateStartPoint, rotateEndPoint);
	      curQuaternion = mesh.quaternion;
	      curQuaternion.multiplyQuaternions(rotateQuaternion, curQuaternion);
	      curQuaternion.normalize();
	      mesh.setRotationFromQuaternion(curQuaternion);

	      rotateEndPoint = rotateStartPoint;

	      return;
	  }

	  function touchHandler(event) {
	      var touches = event.changedTouches,
	          first = touches[0],
	          type = "";
	      switch (event.type) {
	          case "touchstart":
	              type = "mousedown";
	              break;
	          case "touchmove":
	              type = "mousemove";
	              break;
	          case "touchend":
	              type = "mouseup";
	              break;
	          default:
	              return;
	      }

	      var simulatedEvent = document.createEvent("MouseEvent");
	      simulatedEvent.initMouseEvent(type, true, true, window, 1,
	          first.screenX, first.screenY,
	          first.clientX, first.clientY, false,
	          false, false, false, 0 /*left*/ , null);

	      first.target.dispatchEvent(simulatedEvent);
	      event.preventDefault();
	  }

	  function onDocumentMouseDown(event) {
	      if (event.target != document.getElementById('stlCanvas')) return;
	      if (event.button != 0) return;

	      event.preventDefault();

	      document.addEventListener('mousemove', onDocumentMouseMove, true);
	      document.addEventListener('mouseup', onDocumentMouseUp, true);
	      document.addEventListener('mouseout', onDocumentMouseOut, true);

	      startPoint = {
	          x: event.clientX,
	          y: event.clientY
	      };



	      rotateStartPoint = rotateEndPoint = projectOnTrackball(0, 0);
	  }

	  function onDocumentMouseMove(event) {
	      deltaX = event.clientX - startPoint.x;
	      deltaY = event.clientY - startPoint.y;

	      startPoint.x = event.clientX;
	      startPoint.y = event.clientY;

	  }

	  function onDocumentMouseUp(event) {

	      document.removeEventListener('mousemove', onDocumentMouseMove, true);
	      document.removeEventListener('mouseup', onDocumentMouseUp, true);
	      document.removeEventListener('mouseout', onDocumentMouseOut, true);

	      deltaX = 0;
	      deltaY = 0;
	      rotateStartPoint = rotateEndPoint = projectOnTrackball(0, 0);
	  }

	  function onDocumentMouseOut(event) {

	      document.removeEventListener('mousemove', onDocumentMouseMove, true);
	      document.removeEventListener('mouseup', onDocumentMouseUp, true);
	      document.removeEventListener('mouseout', onDocumentMouseOut, true);

	      deltaX = 0;
	      deltaY = 0;
	      rotateStartPoint = rotateEndPoint = projectOnTrackball(0, 0);
	  }

	  function animate() {
	      //will not render if the browser window is inactive
	      requestAnimationFrame(animate);
	      render();
	      onRotate();
	  }

	  function render() {
	      controls.update(); //for cameras
	      renderer.render(scene, camera);
	  }