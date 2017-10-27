Tetris.Shape = function(clock, blocks, offsetPos, colors) {
	var i, k;
	this.clock = clock;
	this.blocks = blocks;
	this.list = {};
	this.width = this.blocks.length;
	this.height = this.blocks[0].length;
	this.width = Math.max(this.blocks.length, this.blocks[0].length);
	this.height = Math.max(this.blocks.length, this.blocks[0].length);
	for (i = 0; i < this.width; i++) {
		for (k = 0; k < this.height; k++) {
			if (this.blocks[i][k]) {
				this.list[i+","+k] = this.blocks[i][k];
			}
		}
	}	
	this.nullPos = new Tetris.Pos(0, 0);
	//this.centerPos = new Tetris.Pos(parseInt((this.width-1) /2), parseInt((this.height-1) /2));
	if (offsetPos) {
		this.moveTo(offsetPos);
	}
	if (colors) {
		this.setColors(colors);
	}
	
	
	this.clock.addTickListener(this);
}

//variables
Tetris.Shape.prototype.clock = undefined;
Tetris.Shape.prototype.nullPos = undefined;
//Tetris.Shape.prototype.centerPos = undefined;
Tetris.Shape.prototype.blocks = undefined;		//Block[][]
Tetris.Shape.prototype.list = undefined;		//Block[]
Tetris.Shape.prototype.isMoving = 0;
Tetris.Shape.prototype.width = undefined;
Tetris.Shape.prototype.height = undefined;

//methods
Tetris.Shape.prototype.setParent = function(domElement) {
	var coord;
	for (coord in this.list) {
		this.list[coord].setParent(domElement);
	}
};
Tetris.Shape.prototype.setColors = function(colors) {
	var coord;
	for (coord in this.list) {
		this.list[coord].setColors(colors);
	}
};
Tetris.Shape.prototype.moveBy = function(pos) {
	var coord;
	for (coord in this.list) {
		this.list[coord].moveBy(pos);
	}
	this.nullPos.x += pos.x;
	this.nullPos.y += pos.y;
};
Tetris.Shape.prototype.moveTo = function(pos) {
	var coord, x, y;
	x = this.nullPos.x;
	y = this.nullPos.y;
	this.nullPos.x = pos.x;
	this.nullPos.y = pos.y;
	for (coord in this.list) {
		this.list[coord].moveTo(new Tetris.Pos(this.list[coord].pos.x - x + pos.x, this.list[coord].pos.y - y + pos.y));
	}
	
};
Tetris.Shape.prototype.rotate = function(clockwise) {
	var r, c, newBlocks = [], newPos = new Tetris.Pos(this.nullPos.x, this.nullPos.y);
    for (r = 0; r < this.width; r++) {
		
        for (c = 0; c < this.height; c++) {
			if (!newBlocks[c]) {
				newBlocks[c] = [];
			}
			if (this.blocks[r][c]) {
				newBlocks[c][this.width - 1 - r] = this.blocks[r][c];
				newPos.x = this.nullPos.x + c;
				newPos.y = this.nullPos.y + this.width - 1 - r;
				this.blocks[r][c].moveTo(newPos);
			} else {
				newBlocks[c][this.width - 1 - r] = Tetris.Board.BLOCK_EMPTY;
			}
        }
    }
    this.blocks = newBlocks;
};
Tetris.Shape.prototype.tick = function(keysPressed) {
	
};
Tetris.Shape.prototype.destroy = function(pos) {
	this.clock.removeTickListener(this);
};