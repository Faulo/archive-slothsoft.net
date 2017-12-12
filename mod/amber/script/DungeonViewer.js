"use strict";

// © 2017 Daniel Schulz

function DungeonViewer(mapNode, tilesetNode) {
	console.log("Amber DungeonViewer 0.1");
	
	this.mapNode = mapNode;
	this.tilesetNode = tilesetNode;
	
	this.init();
}
DungeonViewer.prototype = Object.create(
	Object.prototype, {
		fieldSize 	: { writable : true },
		tileSize : { writable : true },
		
		viewNode 	: { writable : true },
		engine	 	: { writable : true },
		stage 	 	: { writable : true },
		camera	 	: { writable : true },
		loader 		: { writable : true },
		textures 	: { writable : true },
		ticker		: { writable : true },
		
		mapNode 		: { writable : true },
		mapWidth 		: { writable : true },
		mapHeight 		: { writable : true },
		mapPaletteId	: { writable : true },
		mapCenter		: { writable : true },
		
		tilesetNode 		: { writable : true },
		tilesetId 			: { writable : true },
		tilesetImageUrl 	: { writable : true },
		tilesetMaterials 	: { writable : true },
		tilesetLayers 		: { writable : true },
		
		init : {
			value : function() {
				try {
					this.fieldSize = 32;
					this.tileSize = 16;
					
					this.mapWidth = parseInt(this.mapNode.getAttribute("width"));
					this.mapHeight = parseInt(this.mapNode.getAttribute("height"));
					this.mapPaletteId = parseInt(this.mapNode.getAttribute("palette-id"));
					this.mapPaletteId--;
					
					if (this.mapWidth > 100) {
						this.fieldSize = 16;
					}
					
					this.tilesetId = parseInt(this.tilesetNode.getAttribute("id"));
					
					this.tilesetImageUrl = "/getData.php/amber/mod.resource?game=ambermoon&mod=Thalion-v1.05-DE&type=3&name=tileset.icons%2F";
					
					this.tilesetImageUrl += ("000" + this.tilesetId).slice(-3);
					this.tilesetImageUrl += "-";
					this.tilesetImageUrl += ("00" + this.mapPaletteId).slice(-2);
					
					this.initView();
					
					this.initTileset();
					
					this.initMap();
					
					this.initDraw();
				} catch(e) {
					console.log(e);
				}
			}
		},
		initView : {
			value : function() {
				this.viewNode = document.createElementNS(NS.HTML, "canvas");
				this.viewNode.setAttribute("class", "DungeonViewer");
				
				this.engine = new BABYLON.Engine(this.viewNode, true);
				
				this.viewNode.setAttribute("width", "640");
				this.viewNode.setAttribute("height", "480");
				
				this.stage = new BABYLON.Scene(this.engine);
				
				this.mapCenter = new BABYLON.Vector3(this.mapHeight / 2, 0, this.mapWidth / 2);
				
				this.camera = new BABYLON.UniversalCamera(
					'camera',
					new BABYLON.Vector3(this.mapHeight, Math.sqrt(this.mapWidth * this.mapHeight), this.mapWidth / 2),
					this.stage
				);
				this.camera.setTarget(new BABYLON.Vector3(this.mapHeight / 2, 0, this.mapWidth / 2));
				this.camera.cameraRotation.x += 0.12;
				this.camera.attachControl(this.viewNode, false);
				
				var light = new BABYLON.HemisphericLight('light', new BABYLON.Vector3(this.mapHeight / 2, Math.sqrt(this.mapWidth * this.mapHeight), this.mapWidth / 2), this.stage);
				
				this.viewNode.addEventListener(
					"click", 
					(eve) => {
						let pickResult = this.stage.pick(this.stage.pointerX, this.stage.pointerY);
						if (pickResult.hit) {
							//alert(pickResult.pickedMesh);
						}
					},
					false
				);
			}
		},
		initTileset : {
			value : function() {
				this.tilesetMaterials = {};
				
				let material;
				
				material = new BABYLON.StandardMaterial("wallMaterial", this.stage);
				material.diffuseColor = new BABYLON.Color3(1, 1, 1);
				material.specularColor = new BABYLON.Color3(0.5, 0.6, 0.87);
				material.emissiveColor = new BABYLON.Color3(0, 0, 0);
				material.ambientColor = new BABYLON.Color3(0.23, 0.4, 0.53);
				
				this.tilesetMaterials.wall = material;
				
				
				let textureURL = "/getData.php/amber/mod.resource?game=ambermoon&mod=Thalion-v1.05-DE&type=3&name=tileset.floors%2F000-00";
				
				material = new BABYLON.StandardMaterial("floorMaterial", this.stage);
				material.diffuseTexture = new BABYLON.Texture(textureURL, this.stage);
				material.specularTexture = new BABYLON.Texture(textureURL, this.stage);
				material.emissiveTexture = new BABYLON.Texture(textureURL, this.stage);
				material.ambientTexture = new BABYLON.Texture(textureURL, this.stage);
				
				this.tilesetMaterials.floor = material;
				
				
				material = new BABYLON.StandardMaterial("eventMaterial", this.stage);
				material.diffuseColor = new BABYLON.Color3(1, 1, 0);
				material.specularColor = new BABYLON.Color3(0.5, 0.1, 0.87);
				material.emissiveColor = new BABYLON.Color3(0, 0, 0);
				material.ambientColor = new BABYLON.Color3(0.23, 0.4, 0.53);
				
				this.tilesetMaterials.event = material;
			}
		},
		initMap : {
			value : function() {
				let floor = BABYLON.MeshBuilder.CreateTiledGround(
					'floor', {
						xmin : 0,
						xmax : this.mapHeight,
						zmin : 0,
						zmax : this.mapWidth,
						subdivisions : { w:this.mapHeight * 2, h:this.mapWidth * 2},
						precision : {w: 1, h: 1},
					},
					this.stage
				);
				//floor.position = this.mapCenter;
				floor.material = this.tilesetMaterials.floor;
				
				let rootNode = this.mapNode.querySelector("field-map");
				
				let rowNodeList = rootNode.childNodes;
				for (let y = 0; y < rowNodeList.length; y++) {
					let rowNode = rowNodeList[y];
					let fieldNodeList = rowNode.childNodes;
					for (let x = 0; x < fieldNodeList.length; x++) {
						let fieldNode = fieldNodeList[x];
						
						if (fieldNode.hasAttribute("low")) {
							let sprite = this.createTile(x, y, fieldNode.getAttribute("low"));
						}
						if (fieldNode.hasAttribute("high")) {
							let sprite = this.createTile(x, y, fieldNode.getAttribute("high"));
						}
						if (fieldNode.hasAttribute("event")) {
							let sprite = this.createEvent(x, y, fieldNode.getAttribute("event"));
						}
					}
				}
			}
		},
		loadTexture : {
			value : function(url, callback) {
				this.loader
					.add(url, url, {xhrType:"blob"})
					.load(
						() => {
							callback.call(this, this.loader.resources[url].texture);
						}
					);
			}
		},
		getDocument : {
			value : function(url, callback) {
				let req = new XMLHttpRequest();
				req.open("GET", url, true);
				req.addEventListener(
					"load",
					(eve) => {
						callback.call(this, req.responseXML);
					},
					false
				);
				req.send();
			}
		},
		getTilesetTextures : {
			value : function(id) {
				return this.tilesetTexturesList[id];
			}
		},
		setTilesetTextures : {
			value : function(id, textures) {
				this.tilesetTexturesList[id] = textures;
			}
		},
		createTile : {
			value : function(x, y, tileId) {
				let sprite;
				if (tileId > 100) {
					sprite = BABYLON.MeshBuilder.CreateBox(tileId, {}, this.stage);
					sprite.material = this.tilesetMaterials.wall;
				} else {
					sprite = BABYLON.MeshBuilder.CreateSphere(tileId, {diameter : 0.25}, this.stage);;
				}
				
				sprite.position.x = y;
				sprite.position.z = x;
				
				return sprite;
			}
		},
		createEvent : {
			value : function(x, y, eventId) {
				let sprite = BABYLON.MeshBuilder.CreateSphere(
					eventId,
					{diameter : 0.5},
					this.stage
				);
				sprite.material = this.tilesetMaterials.event;
				
				sprite.position.x = y;
				sprite.position.z = x;
				
				sprite.actionManager = new BABYLON.ActionManager(this.stage);
				
				sprite.actionManager.registerAction(
					new BABYLON.ExecuteCodeAction(
						BABYLON.ActionManager.OnPickTrigger,
						(eve) => {
							alert("This is event #" + eventId);
						}
					)
				);
				
				let animationBox = new BABYLON.Animation(
					"myAnimation", "position.y", 60,
					BABYLON.Animation.ANIMATIONTYPE_FLOAT,
					BABYLON.Animation.ANIMATIONLOOPMODE_CYCLE
				);
				let keys = []; 

				//At the animation key 0, the value of scaling is "1"
				  keys.push({
					frame: 0,
					value: 0
				  });

				  //At the animation key 20, the value of scaling is "0.2"
				  keys.push({
					frame: 50,
					value: 0.5
				  });

				  //At the animation key 100, the value of scaling is "1"
				  keys.push({
					frame: 100,
					value: 0
				  });
				animationBox.setKeys(keys);
				
				sprite.animations = [animationBox];
				
				this.stage.beginAnimation(sprite, 0, 100, true);
				
				return sprite;
			}
		},
		getViewNode : {
			value : function() {
				return this.viewNode;
			}
		},
		initDraw : {
			value : function() {
				this.engine.runRenderLoop(
					() => {
						this.draw();
						this.stage.render();
					}
				);
				window.addEventListener(
					'resize',
					(eve) => {
						this.engine.resize();
					}
				);
			}
		},
		draw : {
			value : function() {
			}
		},
	}
);