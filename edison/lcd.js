var lcd = require('jsupm_i2clcd');
var display = new lcd.Jhd1313m1(0, 0x3E, 0x62);
var defaultValue, potValue;
var displayColours = [
	{"r": 255, "g": 255, "b": 255},
	{"r": 136, "g": 196, "b": 37},
	{"r": 249, "g": 212, "b": 35},
	{"r": 252, "g": 145, "b": 58},
	{"r": 153, "g": 1, "b": 0},
	{"r": 0, "g": 0, "b": 0},
];

function initDisplay(print) {
	defaultValue = print;
	resetDisplay();
}

function resetDisplay(){
	display.clear();
	display.setCursor(0,0);
	display.write('Migraine Alert');
	display.setCursor(1,0);
	display.write(defaultValue);
	var colour = displayColours[0];
	display.setColor(colour.r, colour.g, colour.b);
}

function updateDisplay() {
	if(potValue === 0) resetDisplay();
	else {
		display.clear();
		display.setCursor(0,0);
		display.write('Migraine pain:');
		display.setCursor(1,0);
		display.write(potValue + '%');

		var colour;

		if(potValue >= 5 && potValue < 40) {
			colour = displayColours[1];
		} else if(potValue >=40 && potValue < 55) {
			colour = displayColours[2];
		} else if(potValue >= 55 && potValue < 70) {
			colour = displayColours[3];
		} else if(potValue >=70 && potValue < 90) {
			colour = displayColours[4];
		} else {
			colour = displayColours[5];
		}

		display.setColor(colour.r, colour.g, colour.b);
	}
}

function setPotValue(value) {
	potValue = value;
	updateDisplay();
}


module.exports = {
	initDisplay: initDisplay,
	setPotValue: setPotValue
}