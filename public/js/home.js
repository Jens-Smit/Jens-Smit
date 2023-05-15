
	gsap.registerPlugin(ScrollToPlugin);
	
			// Warten bis das Dokument geladen ist
		 document.addEventListener("DOMContentLoaded", function() {
		  // Referenz auf das Div-Element erstellen
		 
		  var glow_1  = document.getElementById("glow_1");
		  var glow_2  = document.getElementById("glow_2");
		  var item_2 = document.getElementById("item_2");
		  var item_1 = document.getElementById("item_1");
		 
		  TweenLite.to(item_1, 3, {rotation:270});
		  TweenLite.to(item_2, 3, {rotation:-270});
		 
		  // GSAP Animation erstellen
		  gsap.to(item_1, {duration: 3, x: (window.innerWidth/2)-158});
		  gsap.to(item_1, {duration: 3, y: (window.innerHeight/2)-100,onComplete: function(){	
				gsap.to(item_1, {duration: 3, rotation: 360});
		  }});
		  gsap.to(item_2, {duration: 3, x: -(window.innerWidth/2)+157});
		  gsap.to(item_2, {duration: 3, y: (window.innerHeight/2)-100,onComplete: function(){
				gsap.to(item_2, {duration: 3, rotation: 180,onComplete: function(){
				var elements = document.getElementsByClassName("item_text");
				for (var i = 0; i < elements.length; i++) {
					gsap.to(elements[i], {duration: 3, opacity: 1,onComplete: function(){
					gsap.to(window, {duration: 2, scrollTo:"#first_section"});

					
					}});
					}
				}});	 
				
		  }});
		});
		
		//animation Section 1
		let ipadValue;
		if (window.innerWidth < 900) {
		  ipadValue = 150;
		} else {
		  ipadValue = 100;
		}
		gsap.to("#ipad", {
			x: ipadValue, // Endposition des Elements
			duration: 2, 
			scrollTrigger: {
				trigger: "#first_section", // Element, das den ScrollTrigger auslöst  
				scrub: 2, // Animation wird mit dem Scrollen synchronisiert
			   // markers: true, // Element wird an die Position gepinnt
			},
		});
		let ipad_headlineValue;
		if (window.innerWidth < 900) {
		  ipad_headlineValue = 0;
		} else {
		  ipad_headlineValue = -400;
		}
		gsap.to("#ipad_headline", {
			
			x: ipad_headlineValue, // Endposition des Elements
			
			
			duration: 2, 
			scrollTrigger: {
				trigger: "#first_section", // Element, das den ScrollTrigger auslöst  
				scrub: 2, // Animation wird mit dem Scrollen synchronisiert
				//markers: true, // Element wird an die Position gepinnt
			},
		});
		// animation Section 2
		let icons_leftValue;
		if (window.innerWidth < 900) {
		  icons_leftValue = 450;
		} else {
		  icons_leftValue = 900;
		}
		gsap.to("#icons_left", {
			x: icons_leftValue, // Endposition des Elements
			duration: 2, 
			scrollTrigger: {
				trigger: "#icons_left", // Element, das den ScrollTrigger auslöst  
				scrub: 2, // Animation wird mit dem Scrollen synchronisiert
			  //  markers: true, // Element wird an die Position gepinnt
			},
		});
		// animation Section 3
		let icons_headlineValue;
		if (window.innerWidth < 900) {
		  icons_headlineValue = -50;
		} else {
		  icons_headlineValue = -100;
		}
		gsap.to("#icons_headline", {
			x: icons_headlineValue, // Endposition des Elements
			duration: 2, 
			scrollTrigger: {
				trigger: "#icons_headline", // Element, das den ScrollTrigger auslöst  
				scrub: 2, // Animation wird mit dem Scrollen synchronisiert
			  //  markers: true, // Element wird an die Position gepinnt
			},
		});

		let icons_headline_2Value;
		if (window.innerWidth < 900) {
		  icons_headline_2Value = 300;
		} else {
		  icons_headline_2Value = 600;
		}
		gsap.to("#icons_headline_2", {
			x: icons_headline_2Value, // Endposition des Elements
			duration: 2, 
			scrollTrigger: {
				trigger: "#icons_headline_2", // Element, das den ScrollTrigger auslöst  
				scrub: 2, // Animation wird mit dem Scrollen synchronisiert
			  //  markers: true, // Element wird an die Position gepinnt
			},
		});
		// animation Section 4
		
		

		let icons_headline_3Value;
		if (window.innerWidth < 900) {
		  icons_headline_3Value = 360;
		} else {
		  icons_headline_3Value = 900;
		}
		gsap.to("#icons_headline_3", {
			x: icons_headline_3Value, // Endposition des Elements
			duration: 2, 
			scrollTrigger: {
				trigger: "#icons_headline_3", // Element, das den ScrollTrigger auslöst  
				scrub: 2, // Animation wird mit dem Scrollen synchronisiert
			  //  markers: true, // Element wird an die Position gepinnt
			},
		});
		