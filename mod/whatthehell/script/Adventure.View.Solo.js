Adventure.View.Solo = Trait.build({
	uses : [
		"ViewGameElement"
	],
	construct : function(ownerView, viewId) {
		this.ViewGameElement(
			ownerView,
			viewId,
			"/getTemplate.php/whatthehell/view.human",
			undefined
		);
		this.ownerView.controller.loadView("map", this);
		this.ownerView.controller.loadView("dropButtons", this);
	},
	vars : {
		lastData : undefined,
	},
	methods : {
		handlePush : function() {
		},
		handlePull : function(uri, data) {
		},
		dropElement : function(name) {
			if (window.viewMapHack) {
				window.viewMapHack.dropElement(name);
			}
		},
	}
});