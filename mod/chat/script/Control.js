// © 2012 Daniel Schulz

function ChatControl() {
	this.formNode = undefined;
	this.templateDoc = undefined;
	this.autoFocus = false;
	this.listNode = undefined;
	this.inputNode = undefined;
	this.lastId = 0;
}
//static
ChatControl.sseURI = "/getData.php/chat/sse";
ChatControl.load = function(eve) {
	try {
		let nodeList = document.querySelectorAll("*[data-chat-id='form']");
		if (nodeList.length) {
			DOMHelper.loadDocument(
				"/getTemplate.php/chat/default",
				(templateDoc) => {
					if (templateDoc) {
						for (let i = 0; i < nodeList.length; i++) {
							let node = nodeList[i];
							try {
								if (node.getBoundingClientRect().height) {
									let chat = new ChatControl();
									chat.init(node, templateDoc, false);
								}
							} catch(e) {
								console.log(e);
							}
						}
					}
				}
			);
		}
	} catch(e) {
		console.log(e);
	}
};
ChatControl.events = {
	sseLog : function(eve) {
		//console.log("%o", eve);
	},
	sseStart : function(eve) {
		this.sseClient._chat.initSSE();
	},
	sseMessage : function(eve) {
		var doc;
		//console.log("%o", eve);
		try {
			if (eve.data) {
				if (doc = DOM.loadXML(eve.data)) {
					this.sseClient._chat.append(doc);
				}
			}
		} catch(e) {
			console.log("%o", e);
		}
	},
	sseDispatched : function(eve) {
		switch (this.status) {
			case 204:
				try {
					this.sseClient._chat.inputNode.value = "";
					this.sseClient._chat.inputNode.disabled = false;
					this.sseClient._chat.inputNode.focus();
				} catch(e) {
				}
				break;
			default:
				//alert(this.responseText);
				break;
		}
	},
};

//instance
ChatControl.prototype.init = function(formElement, templateDoc, autoFocus) {
	var i;
	this.templateDoc = templateDoc;
	this.autoFocus = autoFocus;
	this.formNode = formElement;
	this.formNode.chatControl = this;
	this.formNode.addEventListener(
		"submit",
		function(eve) {
			eve.preventDefault();
			this.chatControl.send();
		},
		false
	);
	this.lastId = XPath.evaluate("number(@data-chat-last-id)", this.formNode);
	this.dbName = XPath.evaluate("string(@data-chat-database)", this.formNode);
	this.listNode = XPath.evaluate(".//*[@data-chat-id='list']", this.formNode)[0];
	this.inputNode = XPath.evaluate(".//*[@data-chat-id='input']", this.formNode)[0];
	this.inputNode.value = "Initializing Server Connection...";
	var scrollOffset = 0;
	for (i = 0; i < this.listNode.childNodes.length; i++) {
		scrollOffset += this.getNodeHeight(this.listNode.childNodes[i]);
	}
	this.listNode.scrollTop += scrollOffset;
	this.sse = new SSE.Client(
		this.constructor.sseURI,
		this.dbName,
		this.constructor.events.sseLog,
		this.lastId
	);
	this.sse._chat = this;
	this.sse.addEventListener("start", this.constructor.events.sseStart);
};
ChatControl.prototype.initSSE = function() {
	this.sse.addEventListener("message", this.constructor.events.sseMessage);
	this.inputNode.value = "";
	this.inputNode.removeAttribute("disabled");
	if (this.autoFocus) {
		this.inputNode.focus();
	}
};
ChatControl.prototype.ajaxCall = function(queryURI, queryContent, callback) {
	var req = new XMLHttpRequest();
	req.chatControl = this;
	req.open("POST", queryURI + "?chat-database=" + this.dbName, true);
	req.setRequestHeader("Content-Type","application/json");//"application/x-www-form-urlencoded"
	req.addEventListener(
		"loadend",
		callback,
		false
	);
	req.send(JSON.stringify(queryContent));
};
ChatControl.prototype.send = function() {
	if (this.inputNode.value.length) {
		this.inputNode.disabled = true;
		this.sse.dispatchEvent(
			"message",
			this.inputNode.value,
			this.constructor.events.sseDispatched
		);
		/*
		this.ajaxCall(
			this.constructor.sendURI,
			this.inputNode.value,
			this.constructor.events.send
		);
		//*/
	}
};
ChatControl.prototype.append = function(dataDoc) {
	var nodeList, i, liList, node;
	this.lastId = XPath.evaluate("number(/data/range/@last-id)", dataDoc);
	nodeList = XSLT.transformToFragment(dataDoc, this.templateDoc, this.listNode.ownerDocument).childNodes;
	liList = [];
	for (i = 0; i < nodeList.length; i++) {
		if (nodeList[i].nodeType === nodeList[i].ELEMENT_NODE) {
			liList.push(nodeList[i]);
		}
	}
	//scrollOffset = 0;
	for (i = 0; i < liList.length; i++) {
		node = liList[i];
		this.listNode.appendChild(node);
		//scrollOffset += this.getNodeHeight(node);
	}
	//this.listNode.scrollTop += scrollOffset;
	if (node) {
		node.scrollIntoView("smooth");
	}
	this.formNode.scrollIntoView("smooth");
};
ChatControl.prototype.getNodeHeight = function(node) {
	return node.offsetHeight;
};

//onload
addEventListener(
	"load",
	ChatControl.load,
	false
);