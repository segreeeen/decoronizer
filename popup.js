// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

"use strict";

//memo
/*
chrome.browserAction.setIcon({path: icon});

*/

const buttonStatus = ["unselected", "selected"];
const statusIconPath = [
  "images/statusicon/inactive.png",
  "images/statusicon/active.png"
];
const language = chrome.i18n.getUILanguage();
let btns = document.getElementsByClassName(buttonStatus[0]);



// debugging:
// chrome.extension.getBackgroundPage().console.log(language);

for (const prop in btns) {
  let item = document.getElementById(btns[prop].id);

  if (item) {
    item.addEventListener("click", () => {
      // toggle this item button class unselected/selected
      item.setAttribute(
        "class",
        buttonStatus[item.getAttribute("class") == buttonStatus[0] ? 1 : 0]
      );

      //set the other button classes to 'unselected'
      deselectTheOtherItems(item.id);

      //get the current selection status
      // let currentStatus = item.getAttribute("class");

      // update status in browser bar 
      chrome.browserAction.setIcon({ path: statusIconPath[item.getAttribute("class") == buttonStatus[1] ? 1 : 0] });

      // debugging:
      // chrome.extension.getBackgroundPage().console.log("buttonStatus: ", item.getAttribute("class"));

      const url = chrome.runtime.getURL(
        "_locales/" + language + "/" + item.id + ".json"
      );
      chrome.extension.getBackgroundPage().console.log(url);
      fetch(url)
        .then(response => response.json()) //assuming file contains json
        .then(json => {
          chrome.storage.local.set({ words: json }, function() {
            // debugging:
            // console.log("Value is set to " + json);
            // chrome.extension.getBackgroundPage().console.log(json);
          });
          // debugging:
          // chrome.extension.getBackgroundPage().console.log(json);
        });
      reload();
    }); // end clickevent
  } // end if item
} // end for

// ----------------------------------------------------------------------------
function deselectTheOtherItems(exeptionId) {
  let selBtns = document.getElementsByClassName(buttonStatus[1]);
  for (let i = 0; i < selBtns.length; i++) {
    let item = document.getElementById(selBtns[i].id);
    if (typeof item.id != "undefined" && item.id != exeptionId) {
      // debugging:
      // chrome.extension.getBackgroundPage().console.log(item);
      selBtns[i].setAttribute("class", buttonStatus[0]);
    }
  }
}
// ----------------------------------------------------------------------------
function reload() {
  chrome.tabs.query({ active: true, currentWindow: true }, function(tab) {
    var code = "window.location.reload();";
    chrome.tabs.executeScript(tab.id, { code: code });
  });
}
// ----------------------------------------------------------------------------

// function changeWord(word) {
//   chrome.tabs.query({ active: true, currentWindow: true }, function(tabs) {
//     chrome.tabs.executeScript(tabs[0].id, {
//       code:
//         `
// 				var findrep = ` + JSON.stringify(word) + `;
//
//         // chrome.extension.getBackgroundPage().console.log('foo');
//
//         findrep.forEach(function(fire) {
//             var highlightedItems = window.document.querySelectorAll("*");
//
//             highlightedItems.forEach(function(item) {
//                 let text = item.innerHTML;
//                 item.innerHTML = text.replace(new RegExp(fire.f, "ig"), fire.r);
//             });
//         });`
//     });
//   });
// }
