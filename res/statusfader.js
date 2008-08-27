var StatusFader = new Class({
	initialize: function(el) {
		this.el = el ? el : $(document).getElement('div.ajax_status');
		this.fx = new Fx.Morph(this.el,{
			link: 'cancel',
			duration: 5000
		});
		this.colors = { good: '#008000', bad: '#ff0000', neutral: '#444' };
		this.messages = { progress: 'Wait&hellip;', success: 'Saved', failure: 'ERROR' }
	},
	set: function(msg) {
		this.el.set('html',this.messages[msg]);
		switch(msg) {
			case 'progress':
				this.el.setStyle('color',this.colors['neutral']);
				this.el.setStyle('padding','6px');
				this.el.setStyle('width','150px');
				this.fx.set({'opacity':1});
			break;
			case 'success':
				this.el.setStyle('color',this.colors['good']);
				this.el.setStyle('padding','6px');
				this.el.setStyle('width','150px');
				this.fx.start({'opacity':[1,0]});
			break;	
			case 'failure':
				this.el.setStyle('color',this.colors['bad']);
				this.el.setStyle('padding','6px');
				this.el.setStyle('width','150px');
				this.fx.start({'opacity':[1,0]});
			break;						
		}
	},
	flash: function (msg, color)
		{
		this.el.set('html', msg);
		this.el.setStyle('color', color);
		this.el.setStyle('padding','6px');
		this.fx.start({'opacity':[1,0]});
		},
	stay: function(msg, color)
		{
		this.el.set('html', msg);
		this.el.setStyle('color', color);
		this.el.setStyle('padding','6px');
		this.fx.set({'opacity':1});
		}
});
