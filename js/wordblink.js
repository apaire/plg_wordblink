function changeOpac(opacity, id) {
    var el = document.getElementById(id);
    if(el) {
		el.style["filter"] = "alpha(opacity=" + opacity + ")";
		el.style["-moz-opacity"] = opacity/100;
		el.style["-khtml-opacity"] = opacity/100;
		el.style["opacity"] = opacity/100;
    }
}

function opacity(id, opacStart, opacEnd, millisec) {
    //speed for each frame
    var speed = Math.round(millisec / 100);
    var timer = 0;

	if(opacStart > opacEnd) {
	for(i = opacStart; i >= opacEnd; i--) {
	            setTimeout("changeOpac(" + i + ",'" + id + "')", (timer * speed));
	            timer++;
	        }
	} else if(opacStart < opacEnd) {
	for(i = opacStart; i <= opacEnd; i++) {
	            setTimeout("changeOpac(" + i + ",'" + id + "')", (timer * speed));
	            timer++;
	        }
	}
}

function wordblink(nbMots, lightOnDuration, transition)
{
	var lightOff = 20;
	var lightOn = 100;
	var period = nbMots * (transition * 2 + lightOnDuration);
	
	for (var i = 0; i < nbMots; i++){
		var idMot = "mot" + i;
		var onOffset = (transition * 2 + lightOnDuration) * i;
		var offOffset = onOffset + lightOnDuration + transition;
		
		// On fait disparaitre tous les mots
		changeOpac(lightOff, idMot)

		if (onOffset == 0) {
			// 1ere occurence d'allumage du mot i
			opacity(idMot, lightOff, lightOn, transition);
			// Programmation périodique d'allumage du mot i
			setInterval("opacity('" + idMot + "', " + lightOff + ", " + lightOn + ", " + transition + ")", period);
		} else {
			// 1ere occurence d'allumage du mot i
			setTimeout("opacity('" + idMot + "', " + lightOff + ", " + lightOn + ", " + transition + ")", onOffset);
			// Programmation périodique d'allumage du mot i
			setTimeout("setInterval(\"opacity('" + idMot+ "', " + lightOff + ", " + lightOn + ", " + transition + ")\", " + period + ")", onOffset);
		}
		// 1ere occurence d'extinction du mot i
		setTimeout("opacity('" + idMot + "', " + lightOn + ", " + lightOff + ", " + transition + ")", offOffset);
		// Programmation périodique d'extinction du mot i
		setTimeout("setInterval(\"opacity('" + idMot + "', " + lightOn + ", " + lightOff + ", " + transition + ")\", " + period + ")", offOffset);
	}
}
