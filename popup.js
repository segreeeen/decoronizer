"use strict";

const buttonStatus = ["button unselected", "button selected"];
const statusIconPath = [
    "images/statusicon/inactive.png",
    "images/statusicon/active.png"
];
const language = chrome.i18n.getUILanguage();
let btns = document.getElementsByClassName("button");

for (const prop in btns) {
    let item = document.getElementById(btns[prop].id);
    if (item) {
        chrome.storage.local.get(['active'], function (result) {
            if (item.getAttribute("id") === result.active) {
                item.setAttribute("class", buttonStatus[1]);
            }
        });
    }

    if (item) {
        item.addEventListener("click", () => {
            chrome.browserAction.setIcon({path: statusIconPath[item.getAttribute("class") == buttonStatus[1] ? 1 : 0]});

            toggleButtonStatus(item);

            const url = chrome.runtime.getURL(
                "_locales/" + language + "/" + item.id + ".json"
            );
            fetch(url)
                .then(response => response.json()) //assuming file contains json
                .then(json => {
                    chrome.storage.local.get(['active'], function (result) {

                        if (result.active !== "inactive") {
                            chrome.storage.local.set({words: json});
                            reload();
                        } else {
                            chrome.storage.local.set({words: "inactive"});
                            reload();
                        }
                    });
                });
        }); // end clickevent
    } // end if item
} // end for

// ----------------------------------------------------------------------------
function toggleButtonStatus(item) {
    chrome.storage.local.get(['active'], function (result) {
        let id = item.getAttribute("id");

        if (id === result.active) {
            chrome.browserAction.setIcon({path: "images/statusicon/inactive.png"});
            item.setAttribute("class", buttonStatus[0]);
            chrome.storage.local.set({active: "inactive"});

        } else {
            chrome.browserAction.setIcon({path: "images/statusicon/active.png"});
            item.setAttribute("class", buttonStatus[1]);
            chrome.storage.local.set({active: id});
            deselectTheOtherItems(id);
        }
    });
}

// ----------------------------------------------------------------------------
function deselectTheOtherItems(exeptionId) {
    let selBtns = document.getElementsByClassName(buttonStatus[1]);
    for (let i = 0; i < selBtns.length; i++) {
        let item = document.getElementById(selBtns[i].id);
        if (typeof item.id != "undefined" && item.id != exeptionId) {
            selBtns[i].setAttribute("class", buttonStatus[0]);
        }
    }
}

// ----------------------------------------------------------------------------
function reload() {
    chrome.tabs.query({active: true, currentWindow: true}, function (tab) {
        var code = "window.location.reload();";
        chrome.tabs.executeScript(tab.id, {code: code});
    });
}
