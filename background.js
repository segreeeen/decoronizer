// Copyright 2018 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

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


