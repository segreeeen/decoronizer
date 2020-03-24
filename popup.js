// Copyright 2018 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

"use strict";

// jedes element mit class button bekommt einen listener 
// bei click wird words je nach locale gesetzt

let language = chrome.i18n.getUILanguage();

let btns = document.getElementsByClassName("button");

chrome.extension.getBackgroundPage().console.log(language);


for (const prop in btns) {
  let item = document.getElementById(btns[prop].id);

  if (item) {
    item.addEventListener("click", () => {
      chrome.extension.getBackgroundPage().console.log(item.id);
      const url = chrome.runtime.getURL('_locales/'+language+'/'+item.id+'.json');
      chrome.extension.getBackgroundPage().console.log(url);
      fetch(url)
          .then((response) => response.json()) //assuming file contains json
          .then((json) => {
            chrome.storage.local.set({words: json}, function() {
              console.log('Value is set to ' + json);
            });
            chrome.extension.getBackgroundPage().console.log(json);
          });
      reload();

    });
  }
}

function reload() {
  chrome.tabs.query({ active: true, currentWindow: true }, function(tab) {
		var code = 'window.location.reload();';
    chrome.tabs.executeScript(tab.id, {code: code});
  });
}


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
