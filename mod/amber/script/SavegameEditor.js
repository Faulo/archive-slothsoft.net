"use strict";

// © 2017 Daniel Schulz
function SavegameEditor(rootNode) {
	this.rootNode = rootNode;
	this.init();
}
SavegameEditor.prototype = Object.create(
	Object.prototype, {
		rootNode : { writable : true },
		templateDoc : {
			writable : true,
		},
		repositoryDoc : {
			writable : true,
		},
		init : {
			value : function() {
				try {
					let nodeList = this.rootNode.querySelectorAll("*[data-template='tabs'] > label > select");
					for (let i = 0; i < nodeList.length; i++) {
						nodeList[i].addEventListener(
							"change",
							this.changeSectionEvent.bind(this),
							false
						);
						nodeList[i].disabled = false;
						//nodeList[i].dispatchEvent(new Event("change"));
						this.changeSectionEvent({ currentTarget : nodeList[i] });
					}
				} catch(e) {
					this.rootNode.textContent = e.message;
				}
				try {
					this.getDocument(
						"/getTemplate.php/amber/editor.data-list",
						(doc) => {
							this.templateDoc = doc;
						}
					)
					this.getDocument(
						"/getData.php/amber/mod.resource?game=ambermoon&mod=Thalion-v1.05-DE&id=libs",
						(doc) => {
							this.repositoryDoc = doc;
						}
					)
				} catch(e) {
					this.rootNode.textContent = e.message;
				}
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
		changeSectionEvent : {
			value : function(eve) {
				try {
					let selectNode = eve.currentTarget;
					let parentNode = selectNode.parentNode.parentNode.lastChild;
					for (let i = 0; i < parentNode.childNodes.length; i++) {
						parentNode.childNodes[i].hidden = (i !== parseInt(selectNode.value));
					}
				} catch(e) {
					selectNode.parentNode.textContent = e.message;
				}
			}
		},
		closePopup : {
			value : function(eve) {
				try {
					let popupNode = document.querySelector(".Amber .popup");
					if (popupNode) {
						while (popupNode.hasChildNodes()) {
							popupNode.removeChild(popupNode.lastChild);
						}
					}
				} catch(e) {
					alert(e);
				}
			},
		},
		openPopup : {
			value : function(eve) {
				try {
					this.closePopup(eve);
					
					let buttonNode = eve.currentTarget;
					//let menuNode = buttonNode.contextMenu;
					let popupNode = document.querySelector(".Amber .popup");
					if (popupNode) {
						let entryId = buttonNode.querySelector("*[value]").getAttribute("value");
						let entryType = buttonNode.getAttribute("type");
						if (entryId && entryType) {
							if (this.templateDoc && this.repositoryDoc) {
								let entryNode = this.getRepositoryElement(entryType, entryId);
								if (entryNode) {
									let articleNode = XSLT.transformToFragment(entryNode, this.templateDoc, document);
									if (articleNode) {
										popupNode.appendChild(articleNode);
									}
								}
							}
						}
						/*
						if (entryType && entryId) {
							let entryNode = document.getElementById(entryType + "-" + entryId);
							if (entryNode && entryNode.content) {
								let articleNode = document.importNode(entryNode.content, true);
								if (articleNode) {
									popupNode.appendChild(articleNode);
								}
							}
						}
						//*/
					}
				} catch(e) {
					alert(e);
				}
			}
		},
		openMenu : {
			value : function(eve) {
				try {
					let pickerNode = document.activeElement;
					let menuNode = eve.currentTarget;
					
					let targetNodeList = pickerNode.children;
					
					for (let i = 0; i < targetNodeList.length; i++) {
						let targetNode = targetNodeList[i];
						
						let name = targetNode.localName;
						let value = targetNode.getAttribute("value");
						let itemNode = menuNode.querySelector("*[data-picker-name='" + name + "']");
						
						if (itemNode) {
							switch (itemNode.type) {
								case "checkbox":
									itemNode.checked = (value !== "");
									break;
								case "radio":
									let filterName = "data-picker-filter-" + name.toLowerCase();
									let itemNodeList = menuNode.querySelectorAll("*[data-picker-name='" + name + "']");
									itemNodeList.forEach(
										(node) => {
											node.checked = false;
											node.disabled = false;
											node.parentNode.hidden = false;
										},
									);
									if (pickerNode.hasAttribute(filterName)) {
										let filterValue = pickerNode.getAttribute(filterName);
										itemNodeList.forEach(
											(node) => {
												node.parentNode.hidden = true;
											}
										);
										itemNodeList.forEach(
											(node) => {
												if (filterValue === node.getAttribute(filterName)) {
													node.parentNode.hidden = false;
												} else {
													node.disabled = true;
												}
											},
										);
									}
									
									itemNode = menuNode.querySelector("*[data-picker-name='" + name + "'][data-picker-value='" + value + "']");
									
									if (itemNode) {
										itemNode.checked = true;
									}
									break;
								default:
									break;
							}
						}
					}
				} catch(e) {
					alert(e);
				}
			}
		},
		closeMenu : {
			value : function(eve) {
				try {
					let pickerNode = document.activeElement;
					let itemNode = eve.currentTarget;
					
					let name = itemNode.getAttribute("data-picker-name");
					
					let targetNode = pickerNode.querySelector(name);
					let inputNode = pickerNode.querySelector(name + " input");
					let value = 0;
					
					if (targetNode && inputNode) {
						switch (itemNode.type) {
							case "checkbox":
								value = itemNode.checked;
								
								targetNode.setAttribute("value", value ? "1" : "");
								inputNode.checked = value;
								break;
							case "radio":
							default:
								value = parseInt(itemNode.getAttribute("data-picker-value"));
								
								targetNode.setAttribute("value", value);
								inputNode.value = value;
								break;
						}
					}
					
					switch(name) {
						case "amber-item-id":
							//pickerNode.setAttribute("data-hover-text", itemNode.getAttribute("label"));
							break;
						case "amber-item-amount":
							if (value === 0) {
								//pickerNode.setAttribute("data-hover-text", "");
								pickerNode.querySelector("amber-item-id").setAttribute("value", "0");
								pickerNode.querySelector("amber-item-id input").value = 0;
							}
							break;
					}
				} catch(e) {
					alert(e);
				}
			}
		},
		/*
		loadForm : {
			value : function(eve) {
				try {
					let detailsNode = eve.currentTarget;
					let templateFile = "/getTemplate.php/amber/editor.savegame";
					if (!detailsNode.hasAttribute("data-form")) {
						detailsNode.setAttribute("data-form", "loading");
						let filename = detailsNode.getAttribute("data-archive-filename");
						let dataNode = document.getElementById("amber-savegame").content.firstChild;
						
						let req = new XMLHttpRequest();
						req.open("GET", this.templateFile, true);
						req.addEventListener(
							"load",
							(eve) => {
								try {
									if (req.responseXML) {
										let xslt = new XSLTProcessor();
										xslt.importStylesheet(req.responseXML);
										xslt.setParameter("", "ARCHIVE_FILENAME", filename);
										let fragment = xslt.transformToFragment(dataNode, document);
										
										detailsNode.appendChild(fragment);
										detailsNode.setAttribute("data-form", "done");
										
										new SavegameEditor(detailsNode);
									}
								} catch(e) {
									alert(e);
								}
							},
							false
						);
						req.send();
					}
				} catch(e) {
					alert(e);
				}
			}
		},
		//*/
		setEquipment : {
			value : function(buttonNode) {
				try {
					let characterNode = buttonNode.parentNode.parentNode.parentNode.parentNode;
					let itemList = characterNode.querySelectorAll(".equipment amber-picker");
					let attributeList = characterNode.querySelectorAll(".attributes tr");
					let skillList = characterNode.querySelectorAll(".skills tr");
					
					let mappings = {};
					mappings["lp-max"] = characterNode.querySelector("*[data-name='hit-points'] input[data-name='maximum-mod']");
					mappings["sp-max"] = characterNode.querySelector("*[data-name='spell-points'] input[data-name='maximum-mod']");
					mappings["hands"] = characterNode.querySelector("select[data-name='hand']");
					mappings["fingers"] = characterNode.querySelector("select[data-name='finger']");
					mappings["damage"] = characterNode.querySelector("input[data-name='attack']");
					mappings["armor"] = characterNode.querySelector("input[data-name='defense']");
					mappings["magic-weapon"] = characterNode.querySelector("input[data-name='magic-attack']");
					mappings["magic-armor"] = characterNode.querySelector("input[data-name='magic-defense']");
					//Attribute
					mappings["Stärke"] 				= characterNode.querySelectorAll(".attributes tr")[0].querySelector("input[data-name='current-mod']");
					mappings["Intelligenz"] 		= characterNode.querySelectorAll(".attributes tr")[1].querySelector("input[data-name='current-mod']");
					mappings["Geschicklichkeit"] 	= characterNode.querySelectorAll(".attributes tr")[2].querySelector("input[data-name='current-mod']");
					mappings["Schnelligkeit"] 		= characterNode.querySelectorAll(".attributes tr")[3].querySelector("input[data-name='current-mod']");
					mappings["Konstitution"] 		= characterNode.querySelectorAll(".attributes tr")[4].querySelector("input[data-name='current-mod']");
					mappings["Karisma"] 			= characterNode.querySelectorAll(".attributes tr")[5].querySelector("input[data-name='current-mod']");
					mappings["Glück"] 				= characterNode.querySelectorAll(".attributes tr")[6].querySelector("input[data-name='current-mod']");
					mappings["Anti-Magie"] 			= characterNode.querySelectorAll(".attributes tr")[7].querySelector("input[data-name='current-mod']");
					//Skills
					mappings["Attacke"] 			= characterNode.querySelectorAll(".skills tr")[0].querySelector("input[data-name='current-mod']");
					mappings["Parade"] 				= characterNode.querySelectorAll(".skills tr")[1].querySelector("input[data-name='current-mod']");
					mappings["Schwimmen"] 			= characterNode.querySelectorAll(".skills tr")[2].querySelector("input[data-name='current-mod']");
					mappings["Kritische Treffer"] 	= characterNode.querySelectorAll(".skills tr")[3].querySelector("input[data-name='current-mod']");
					mappings["Fallen Finden"] 		= characterNode.querySelectorAll(".skills tr")[4].querySelector("input[data-name='current-mod']");
					mappings["Fallen Entschärfen"] 	= characterNode.querySelectorAll(".skills tr")[5].querySelector("input[data-name='current-mod']");
					mappings["Schlösser Knacken"] 	= characterNode.querySelectorAll(".skills tr")[6].querySelector("input[data-name='current-mod']");
					mappings["Suchen"] 				= characterNode.querySelectorAll(".skills tr")[7].querySelector("input[data-name='current-mod']");
					mappings["Spruchrollen Lesen"] 	= characterNode.querySelectorAll(".skills tr")[8].querySelector("input[data-name='current-mod']");
					mappings["Magie Benutzen"] 		= characterNode.querySelectorAll(".skills tr")[9].querySelector("input[data-name='current-mod']");
					
					let data = {};
					for (let key in mappings) {
						data[key] = 0;
					}
					
					for (let i = 0; i < itemList.length; i++) {
						let itemId = itemList[i].querySelector("amber-item-id input").value;
						if (itemId) {
							let itemNode = this.getItemById(itemId);
							if (itemNode) {
								if (itemNode) {
									for (let key in data) {
										if (itemNode.hasAttribute(key)) {
											data[key] += parseInt(itemNode.getAttribute(key));
										}
									}
									if (itemNode.getAttribute("attribute-type")) {
										data[itemNode.getAttribute("attribute-type")] += parseInt(itemNode.getAttribute("attribute-value"));
									}
									if (itemNode.getAttribute("skill-type")) {
										data[itemNode.getAttribute("skill-type")] += parseInt(itemNode.getAttribute("skill-value"));
									}
								}
							}
						}
					}
					
					for (let key in mappings) {
						//alert(key + "\n" + mappings[key]);
						if (mappings[key]) {
							mappings[key].value = data[key];
						}
					}
				} catch(e) {
					alert(e);
				}
			}
		},
		getItemById : {
			value : function(itemId) {
				return this.getRepositoryElement("item", itemId);
			}
		},
		getRepositoryElement : {
			value : function(entryType, entryId) {
				let ret = null;
				try {
					if (this.repositoryDoc) {
						ret = this.repositoryDoc.querySelector(entryType + "[id='" + entryId + "']")
					}
				} catch(e) {
					alert(e);
				}
				return ret;
			}
		},
		viewMap : {
			value : function(detailsNode) {
				try {
					if (!detailsNode._mapViewer) {
						let mapNode = detailsNode.querySelector("template").content.querySelector("map");
						if (mapNode) {
							let tilesetId, tilesetNode, viewer;
							tilesetId = mapNode.getAttribute("tileset-id");
							switch (mapNode.getAttribute("map-type")) {
								case "2":
								case "2D":
									tilesetNode = this.getRepositoryElement("tileset-icon", tilesetId);
									viewer = "MapViewer";
									break;
								case "1":
								case "3D":
									tilesetNode = this.getRepositoryElement("tileset-lab", tilesetId);
									viewer = "DungeonViewer";
									break;
							}
							if (tilesetNode) {
								detailsNode._mapViewer = new window[viewer](mapNode, tilesetNode);
								detailsNode.appendChild(detailsNode._mapViewer.getViewNode());
							}
						}
					}
				} catch(e) {
					detailsNode.textContent = e;
				}
			}
		},
	}
);