//    Copyright (C) 2020 Boris Baumann, Felix Batusic

//    This file is part of DeCoronizer.
//
//    DeCoronizer is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.

//    DeCoronizer is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.

//    You should have received a copy of the GNU General Public License
//    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

'use strict';

// set decoro-status inactive
chrome.browserAction.setIcon({ path: "images/statusicon/inactive.png"});


chrome.runtime.onInstalled.addListener(function () {
    chrome.declarativeContent.onPageChanged.removeRules(undefined, function () {
        chrome.declarativeContent.onPageChanged.addRules([{
            conditions: [new chrome.declarativeContent.PageStateMatcher()],
            actions: [new chrome.declarativeContent.ShowPageAction()]
        }]);
    });
});

chrome.tabs.onUpdated.addListener(function (tabId, changeInfo, tab) {
    chrome.storage.local.get(['words'], function (result) {
        if (result.words !== "inactive") {
            console.log('Value currently is ' + result.words);
            chrome.tabs.executeScript(tabId, {
                code:
                `var findrep = ` + JSON.stringify(result.words) + `;

                // chrome.extension.getBackgroundPage().console.log('foo');
                findrep.forEach(function(fire) {
                    replaceTextNodes(document.getRootNode(), fire);
                });
                
                function replaceTextNodes(node, fire) {
                    node.childNodes.forEach(function(el) {
                        if (el.nodeType === 3) {  // If this is a text node, replace the text
                            if (el.nodeValue.trim() !== "") { // Ignore this node it it an empty text node
                                let text = el.nodeValue;
                                el.nodeValue = text.replace(new RegExp(fire.f, "ig"), fire.r);
                            }
                        } else { // Else recurse on this node
                            replaceTextNodes(el, fire);
                        }
                    });
                }`
            });
        }
    });
});


