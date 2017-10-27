// © 2012 Daniel Schulz

function ChatControl() {
	this.formNode = undefined;
	this.templateDoc = undefined;
	this.listNode = undefined;
	this.inputNode = undefined;
	this.lastTime = 0;
}
//static
ChatControl.pullURI = "/getData.php/chat/watch";
ChatControl.sendURI = "/getData.php/chat/insert";
ChatControl.load = function(eve) {
	var arr, chat, req, doc, node;
	try {
		/*
		removeEventListener(
			"load",
			arguments.callee,
			false
		);
		//*/
		arr = XPath.evaluate("//*[@data-chat-id='form']", document);
		while(arr.length) {
			try {
				node = arr.pop();
				if (node.getBoundingClientRect().height) {
					if (!doc) {
						doc = DOM.loadDocument("/getTemplate.php/chat/default");
					}
					if (doc) {
						chat = new ChatControl();
						//chat.init(arr.pop(), doc, arr.length === 0);
						chat.init(node, doc, false);
					}
				}
			} catch(e) {
			}
		}
	} catch(e) {
	}
};
ChatControl.events = {
	send : function(eve) {
		switch (this.status) {
			case 204:
				try {
					this.chatControl.inputNode.value = "";
					this.chatControl.inputNode.removeAttribute("disabled");
					this.chatControl.inputNode.focus();
				} catch(e) {
				}
				break;
			default:
				//alert(this.responseText);
				break;
		}
	},
	pull : function(eve) {
		switch (this.status) {
			case 200:
				try {
					this.chatControl.append(this.responseXML);
					this.chatControl.pull();
				} catch(e) {
				}
				break;
			default:
				//alert(this.responseText);
				break;
		}
	}
};

//instance
ChatControl.prototype.init = function(formElement, templateDoc, autoFocus) {
	var i;
	this.templateDoc = templateDoc;
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
	this.lastTime = XPath.evaluate("number(@data-chat-time)", this.formNode);
	this.dbName = XPath.evaluate("string(@data-chat-database)", this.formNode);
	this.listNode = XPath.evaluate(".//*[@data-chat-id='list']", this.formNode)[0];
	this.inputNode = XPath.evaluate(".//*[@data-chat-id='input']", this.formNode)[0];
	scrollOffset = 0;
	for (i = 0; i < this.listNode.childNodes.length; i++) {
		scrollOffset += this.getNodeHeight(this.listNode.childNodes[i]);
	}
	this.listNode.scrollTop += scrollOffset;
	this.pull();
	this.inputNode.removeAttribute("disabled");
	if (autoFocus) {
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
		this.inputNode.setAttribute("disabled", "disabled");
		this.ajaxCall(
			this.constructor.sendURI,
			this.inputNode.value,
			this.constructor.events.send
		);
	}
};
ChatControl.prototype.pull = function() {
	this.ajaxCall(
		this.constructor.pullURI,
		this.lastTime,
		this.constructor.events.pull
	);
};
ChatControl.prototype.append = function(dataDoc) {
	var nodeList, i, liList, node, scrollOffset;
	this.lastTime = XPath.evaluate("number(/data/range/@last-time)", dataDoc);
	nodeList = XSLT.transformToFragment(dataDoc, this.templateDoc, this.listNode.ownerDocument).childNodes;
	liList = [];
	for (i = 0; i < nodeList.length; i++) {
		if (nodeList[i].nodeType === nodeList[i].ELEMENT_NODE) {
			liList.push(nodeList[i]);
		}
	}
	scrollOffset = 0;
	for (i = 0; i < liList.length; i++) {
		node = liList[i];
		this.listNode.appendChild(node);
		scrollOffset += this.getNodeHeight(node);
	}
	this.listNode.scrollTop += scrollOffset;
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