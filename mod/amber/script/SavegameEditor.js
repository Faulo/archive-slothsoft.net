// © 2017 Daniel Schulz
function SavegameEditor(rootNode) {
	this.rootNode = rootNode;
	this.init();
}
SavegameEditor.prototype = Object.create(
	Object.prototype, {
		rootNode : { writable : true },
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
					let menuNode = buttonNode.contextMenu;
					let popupNode = document.querySelector(".Amber .popup");
					if (popupNode) {
						let entryType = buttonNode.getAttribute("contextmenu");
						let entryId = buttonNode.querySelector("input").value;
						if (entryType && entryId) {
							let entryNode = document.getElementById(entryType + "-" + entryId);
							if (entryNode && entryNode.content) {
								let articleNode = document.importNode(entryNode.content, true);
								if (articleNode) {
									popupNode.appendChild(articleNode);
								}
							}
						}
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
					
					let targetNodeList = pickerNode.querySelectorAll("*[data-picker-value]");
					
					for (let i = 0; i < targetNodeList.length; i++) {
						let targetNode = targetNodeList[i];
						
						let name = targetNode.getAttribute("data-picker-name");
						let value = targetNode.getAttribute("data-picker-value");
						
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
					
					let targetNode = pickerNode.querySelector("*[data-picker-name='" + name + "']");
					let inputNode = pickerNode.querySelector("*[data-picker-name='" + name + "'] input");
					
					if (targetNode && inputNode) {
						let value;
						switch (itemNode.type) {
							case "checkbox":
								value = itemNode.checked;
								
								targetNode.setAttribute("data-picker-value", value ? "1" : "");
								inputNode.checked = value;
								break;
							case "radio":
							default:
								value = itemNode.getAttribute("data-picker-value");
								
								targetNode.setAttribute("data-picker-value", value);
								inputNode.value = value;
								break;
						}
					}
					
					switch(name) {
						case "item-id":
							pickerNode.setAttribute("data-hover-text", itemNode.getAttribute("label"));
							break;
					}
				} catch(e) {
					alert(e);
				}
			}
		},
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
		setEquipment : {
			value : function(buttonNode) {
				try {
					let characterNode = buttonNode.parentNode.parentNode.parentNode.parentNode;
					let itemList = characterNode.querySelectorAll(".equipment .item");
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
						let itemId = itemList[i].querySelector("*[data-picker-name='item-id'] input").value;
						if (itemId) {
							let itemNode = document.getElementById("item-" + itemId);
							if (itemNode) {
								itemNode = itemNode.content.querySelector("item");
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
	}
);