// © 2012 Daniel Schulz

var Amber = {
	Data : undefined,
	chars : undefined,
	selectList : undefined,
	activeCharNo : undefined,
	activeChar : undefined,
	load : function(data) {
		var i, j, arr, node, inputRes, child;
		this.Data = data;
		this.selectList = document.getElementsByClassName("Team")[0].childNodes;
		arr = document.getElementsByClassName("Char");
		this.chars = [];
		for (i = 0; i < arr.length; i++) {
			this.chars[i] = arr[i];
			//Rasse + Attribute
			node = this.chars[i].getElementsByClassName("Rasse")[0];
			node.statMax = this.Data["AttributesMax"];
			node.charNode = this.chars[i];
			this.chars[i].raceNode = node;
			node.inputs = {
				"Cur" : [],
				"Max" : [],
				"CurMod" : [],
				"MaxMod" : []
			};
			inputRes = node.charNode.getElementsByClassName("Stats")[0].childNodes[1].getElementsByTagName("input");
			for (j = 0; j < inputRes.length; j++) {
				child = inputRes[j];
				if (node.inputs[child.getAttribute("class")]) {
					node.inputs[child.getAttribute("class")].push(child);
				}
			}
			node.inputs.Cur.push(0);
			node.inputs.Max.push(0);
			inputRes = node.charNode.getElementsByClassName("Alter")[0];
			node.inputs.Cur.push(inputRes.firstChild);
			node.inputs.Max.push(inputRes.lastChild);
			node.amber = this;
			node.addEventListener(
				"change",
				function(eve) {
					this.amber.changeMax(this);
				},
				false
			);
			//this.changeMax(node);
			
			//Klasse + Skills
			node = this.chars[i].getElementsByClassName("Klasse")[0];
			node.statMax = this.Data["SkillsMax"];
			node.charNode = this.chars[i];
			this.chars[i].classNode = node;
			node.inputs = {
				"Cur" : [],
				"Max" : [],
				"CurMod" : [],
				"MaxMod" : []
			};
			inputRes = node.charNode.getElementsByClassName("Stats")[0].childNodes[3].getElementsByTagName("input");
			for (j = 0; j < inputRes.length; j++) {
				child = inputRes[j];
				if (node.inputs[child.getAttribute("class")]) {
					node.inputs[child.getAttribute("class")].push(child);
				}
			}
			node.inputs.Max.push(0);
			node.inputs.Max.push(node.charNode.getElementsByClassName("LPproLvl")[0]);
			node.inputs.Max.push(node.charNode.getElementsByClassName("SPproLvl")[0]);
			node.inputs.Max.push(node.charNode.getElementsByClassName("TPproLvl")[0]);
			node.inputs.Max.push(node.charNode.getElementsByClassName("SLPproLvl")[0]);
			node.inputs.Max.push(node.charNode.getElementsByClassName("APRproLvl")[0]);
			node.amber = this;
			node.addEventListener(
				"change",
				function(eve) {
					this.amber.changeMax(this);
				},
				false
			);
			//this.changeMax(node);
		}
	},
	changeMax : function(node) {
		var i, j, arr, val;
		if (node.statMax[node.selectedIndex]) {
			arr = node.statMax[node.selectedIndex];
			for (i = 0, j = node.inputs.Max.length; i < j; i++) {
				if (node.inputs.Max[i] && typeof arr[i] !== "undefined") {
					node.inputs.Max[i].value = arr[i];
					if (node.inputs.Cur[i] && parseInt(node.inputs.Cur[i].value) > parseInt(node.inputs.Max[i].value)) {
						node.inputs.Cur[i].value = node.inputs.Max[i].value;
					}
				}
			}
		}
		
		/*
		arr = document.getElementsByName(this.name);
		for (i in arr) {
			if (arr[i] === this) {
				member = i;
				break;
			}
		}
		try {
			val = parseInt(this.options[this.selectedIndex].getAttribute("value"));
			data = Amber.Data[key+"s" + "Max"][val];
		} catch(e) {
			val = -1;
			data = [];
		}
		for (i = 0, j = Amber.Data[key+"s"].length; i < j; i++) {
			arr = document.getElementsByName(key + "Max_" + i + "_" + member)[0];
			try {
				tmp = data[i];
			} catch(e) {
				tmp = 0;
			}
			arr.setAttribute("value", tmp);
			arr = document.getElementsByName(key + "Cur_" + i + "_" + member)[0];
			if (parseInt(arr.getAttribute("value")) > tmp) {
				arr.setAttribute("value", tmp);
			}
		}
		try {
			tmp = data[i];
		} catch(e) {
			tmp = 0;
		}
				
		arr = document.getElementsByClassName("CHAR_POINTS_" + key+"s")[member];
		arr.nextSibling.innerHTML = tmp;
		
		if (data) {
			tmp = i+1;
			for (i = 0, j = data.length - tmp; i < j; i++) {
				try {
					arr = document.getElementsByClassName("CHAR_ADDITIONAL_" + key+"s_"+i)[member];
					arr.setAttribute("value", data[tmp + i]);
				} catch(e) {
				}
			}
		}
		
		Amber.syncList.call(document.getElementsByClassName("CHAR_LIST_"+key+"s"+member)[0], key, member);
		//*/
	},
	changeChar : function(key) {
		var i, j;
		for (i = 0, j = this.selectList.length; i < j; i++) {
			if (i == key) {
				this.activeChar = this.selectList[i];
				this.selectList[i].removeAttribute("style");
			} else {
				this.selectList[i].setAttribute("style", "display: none");
			}
		}
	},
	syncValue : function(className, nr) {
		var i, j, arr = document.getElementsByClassName(className + (nr + 1));

		for (i = 0, j = arr.length; i < j ; i++) {
			arr[i].firstChild.data = this.value;
		}
	},
	attrKeys : [0,1,2,3,4,5,6,7],
	rollAttributes : function() {
		var i, j, maxList, curList, key, val;
		curList = this.activeChar.raceNode.inputs.Cur;
		maxList = this.activeChar.raceNode.inputs.Max;
		for (i = 0; i < this.attrKeys.length; i++) {
			key = this.attrKeys[i];
			val = parseInt(maxList[key].value);
			val = parseInt(val / 4 + this.xdy(3, val / 4));
			if (val > maxList[key].value) {
				val = maxList[key].value;
			}
			curList[key].value = val;
		}
	},
	skillKeys : [0,1,2,3,4,5,6,7,8,9],
	rollSkills : function(member) {
		var i, j, maxList, curList, key, val;
		curList = this.activeChar.classNode.inputs.Cur;
		maxList = this.activeChar.classNode.inputs.Max;
		for (i = 0; i < this.skillKeys.length; i++) {
			key = this.skillKeys[i];
			val = parseInt(maxList[key].value);
			val = parseInt(val / 5 + this.xdy(3, val / 5));
			if (val > maxList[key].value) {
				val = maxList[key].value;
			}
			curList[key].value = val;
		}
	},
	xdy : function(x, y) {
		var i, j, ret = 0;
		for (i = 0; i < x; i++) {
			ret += 1 + parseInt( Math.random() * y)
		}
		return ret;
	}
};