var BLOCK_SIZE = 16;

Tetris.Block = function(clock, pos) {
	this.clock = clock;
	this.pos = pos;
	this.dom = {
		parent : undefined,
		root : undefined
	};
	
	//this.dom.css = this.dom.root.style.getPropertyCSSValue("left");
	//this.dom.cssY = this.dom.root.style.getPropertyCSSValue("top");
	//this.dom.cssColor = this.dom.root.style.getPropertyCSSValue("background-color");
	this.colorArr = [0,0,0];
};

//variables
Tetris.Block.prototype.clock = undefined;
Tetris.Block.prototype.pos = undefined;
Tetris.Block.prototype.dom = undefined;
Tetris.Block.prototype.colorArr = undefined;
Tetris.Block.prototype.colorChanged = true;
Tetris.Block.prototype.movedX = true;
Tetris.Block.prototype.movedY = true;
Tetris.Block.prototype.moveX = 0;
Tetris.Block.prototype.moveY = 0;

//methods
Tetris.Block.prototype.setParent = function(domElement) {
	this.dom.parent = domElement;
	if (!this.dom.root) {
		this.dom.root = this.dom.parent.ownerDocument.createElement("div");
		this.dom.root.setAttribute("style", "left: 0px; top: 0px; background-color: rgb(0,0,0);");
		this.clock.addDrawListener(this);
	}
	this.dom.parent.appendChild(this.dom.root);
};
Tetris.Block.prototype.moveBy = function(pos) {
	this.movedX = this.movedX || (pos.x !== 0);
	this.movedY = this.movedY || (pos.y !== 0);
	this.pos.x += pos.x;
	this.pos.y += pos.y;
	this.moveX = pos.x * BLOCK_SIZE;
	this.moveY = pos.y * BLOCK_SIZE;
};
Tetris.Block.prototype.moveTo = function(pos) {
	this.movedX = this.movedX || (pos.x !== this.pos.x);
	this.movedY = this.movedY || (pos.y !== this.pos.y);
	this.pos.x = pos.x;
	this.pos.y = pos.y;
	this.moveX = 0;
	this.moveY = 0;
};
Tetris.Block.prototype.setColors = function(colors) {
	this.colorArr = colors;
	this.colorChanged = true;
};
Tetris.Block.prototype.draw = function() {
	//this.dom.root.setAttribute("style", "left: "+spriteX+"px; top: "+spriteY+"px;");
	if (this.movedX) {
		var spriteX = BLOCK_SIZE*this.pos.x - this.moveX;
		this.dom.root.style.setProperty("left", spriteX + "px", "");
		if (this.moveX === 0) {
			this.movedX = false;
		}
		if (this.moveX > 0) {
			this.moveX--;
		}
		if (this.moveX < 0) {
			this.moveX++;
		}
	}
	if (this.movedY) {
		var spriteY = BLOCK_SIZE*this.pos.y - this.moveY;
		this.dom.root.style.setProperty("top", spriteY + "px", "");
		if (this.moveY === 0) {
			this.movedY = false;
		}
		if (this.moveY > 0) {
			this.moveY--;
		}
		if (this.moveY < 0) {
			this.moveY++;
		}
	}
	if (this.colorChanged) {
		//this.dom.root.setAttribute("style", "background-color:rgb("+this.colorArr.join(",")+");");
		this.dom.root.style.setProperty("background-color", "rgb("+this.colorArr.join(",")+")", "");
		this.colorChanged = false;
	}
};
Tetris.Block.prototype.destroy = function(pos) {
	if (this.dom.root) {
		if (this.dom.root.parentNode) {
			this.dom.root.parentNode.removeChild(this.dom.root);
		}
		this.clock.removeDrawListener(this);
	}
};