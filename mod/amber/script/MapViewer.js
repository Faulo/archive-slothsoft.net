// © 2017 Daniel Schulz
function MapViewer(mapNode, tilesetNode) {
	PIXI.utils.sayHello("Amber MapViewer 0.1");
	
	this.mapNode = mapNode;
	this.tilesetNode = tilesetNode;
	
	this.init();
}
MapViewer.prototype = Object.create(
	Object.prototype, {
		fieldSize 	: { writable : true },
		tileSize : { writable : true },
		
		viewNode 	: { writable : true },
		renderer 	: { writable : true },
		stage 	 	: { writable : true },
		loader 		: { writable : true },
		textures 	: { writable : true },
		ticker		: { writable : true },
		
		mapNode 		: { writable : true },
		mapWidth 		: { writable : true },
		mapHeight 		: { writable : true },
		mapPaletteId	: { writable : true },
		
		tilesetNode 		: { writable : true },
		tilesetId 			: { writable : true },
		tilesetImageUrl 	: { writable : true },
		tilesetTexturesList : { writable : true },
		staticTiles 		: { writable : true },
		animatedTiles 		: { writable : true },
		
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
					
					this.tilesetImageUrl = "http://localhost/getData.php/amber/mod.resource?game=ambermoon&mod=Thalion-v1.05-DE&type=3&name=tileset.icons%2F";
					
					this.tilesetImageUrl += ("000" + this.tilesetId).slice(-3);
					this.tilesetImageUrl += "-";
					this.tilesetImageUrl += ("00" + this.mapPaletteId).slice(-2);
					
					this.initPIXI();
					
					this.loadTexture(
						this.tilesetImageUrl,
						(texture) => {
							this.initTileset(texture);
							
							this.initMap();
							
							this.initDraw();
						}
					);
				} catch(e) {
					console.log(e);
				}
			}
		},
		initPIXI : {
			value : function() {
				this.renderer = new PIXI.CanvasRenderer(
					this.mapWidth * this.fieldSize, this.mapHeight * this.fieldSize,
					{antialias: false, transparent: false, resolution: 1}
				);
				//this.renderer.autoResize = true;
				
				this.viewNode = this.renderer.view;
				
				this.stage = new PIXI.Container();
				this.renderer.render(this.stage);
				
				this.loader = new PIXI.loaders.Loader();
					
				this.loader.use(
					(resource, next) => {
						resource.extension = "png";
						next();
					}
				);
				
				this.textures = PIXI.TextureCache;
				
				PIXI.SCALE_MODES.DEFAULT = PIXI.SCALE_MODES.NEAREST;
				
				this.ticker = new PIXI.ticker.Ticker();
			}
		},
		initTileset : {
			value : function(tilesetTexture) {
				this.tilesetTexturesList = {};
				this.staticTiles = new PIXI.Container();
				this.staticTiles.cacheAsBitmap = true;
				this.animatedTiles = new PIXI.Container();
				
				this.stage.addChild(this.staticTiles);
				this.stage.addChild(this.animatedTiles);
				
				let nodeList = this.tilesetNode.querySelectorAll("tile");
				for (let i = 0; i < nodeList.length; i++) {
					let node = nodeList[i];
					let tileId = node.getAttribute("id");
					let imageId = parseInt(node.getAttribute("image-id"));
					let imageCount = parseInt(node.getAttribute("image-count"));
					
					if (imageCount > 0) {
						let textures = [];
						for (let imageCounter = 0; imageCounter < imageCount; imageCounter++) {
							let texture = new PIXI.Texture(tilesetTexture.baseTexture, {
								x: 0,
								y: (imageId - 1 + imageCounter) * this.tileSize,
								width: this.tileSize,
								height: this.tileSize
							});
							textures.push(texture);
							//PIXI.Texture.addToCache(texture, tileId);
						}
						this.setTilesetTextures(tileId, textures);
					}
				}
			}
		},
		initMap : {
			value : function() {
				let rootNode = this.mapNode.querySelector("field-map");
				
				let rowNodeList = rootNode.childNodes;
				for (let y = 0; y < rowNodeList.length; y++) {
					let rowNode = rowNodeList[y];
					
					let fieldNodeList = rowNode.childNodes;
					for (let x = 0; x < fieldNodeList.length; x++) {
						let fieldNode = fieldNodeList[x];
						
						if (fieldNode.hasAttribute("low")) {
							this.addTile(x, y, fieldNode.getAttribute("low"));
						}
						if (fieldNode.hasAttribute("high")) {
							this.addTile(x, y, fieldNode.getAttribute("high"));
						}
						if (fieldNode.hasAttribute("event")) {
							this.addText(x, y, fieldNode.getAttribute("event"));
						}
					}
				}
				
				this.renderer.render(this.stage);
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
		addTile : {
			value : function(x, y, tileId) {
				let textures = this.getTilesetTextures(tileId);
				let sprite;
				if (textures.length === 1) { 
					sprite = new PIXI.Sprite(textures[0]);
					//sprite.cacheAsBitmap = true;
					
					this.staticTiles.addChild(sprite);
				} else {
					sprite = new PIXI.extras.AnimatedSprite(textures, true);
					sprite.animationSpeed = 0.1;
					sprite.loop = true;
					window.setTimeout(
						() => {
							sprite.play();
						},
						100 * Math.random()
					);
					
					this.animatedTiles.addChild(sprite);
				}

				sprite.x = x * this.fieldSize;
				sprite.y = y * this.fieldSize;
				
				sprite.scale.set(this.fieldSize / this.tileSize, this.fieldSize / this.tileSize);
			}
		},
		addText : {
			value : function(x, y, text) {
				let sprite = new PIXI.Text(
					text,
					{ fontFamily: "myOldschool", fill : "rgb(255, 153, 0)", fontWeight: "bold", fontSize: this.fieldSize * 0.75 }
				);

				sprite.x = x * this.fieldSize;
				sprite.y = y * this.fieldSize;
				
				sprite.alpha = 0.8;
				sprite.interactive = true;
				sprite.buttonMode = true;

				this.staticTiles.addChild(sprite);
			}
		},
		getViewNode : {
			value : function() {
				return this.viewNode;
			}
		},
		initDraw : {
			value : function() {
				this.ticker.stop();
				this.ticker.add((deltaTime) => {
					this.draw();
					this.renderer.render(this.stage);
				});
				window.setTimeout(
					() => {
						this.ticker.start();
					},
					0
				);
			}
		},
		draw : {
			value : function() {
			}
		},
	}
);