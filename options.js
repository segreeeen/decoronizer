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

let page = document.getElementById('inputDiv');

function constructOptions() {
  let input = document.createElement('input');
  let button = document.createElement('input');
  button.setAttribute('type', 'button');
  button.value = "Speichern";
  input.setAttribute("type", "text");
  button.addEventListener('click', () => {
    let word = input.value;
    chrome.storage.sync.set({word: word}, () => {
      console.log('word is ' + word);
    });
  });
  page.appendChild(input);
  page.appendChild(button);
}
constructOptions();


/*let page = document.getElementById('buttonDiv');
const kButtonColors = ['#3aa757', '#e8453c', '#f9bb2d', '#4688f1'];
function constructOptions(kButtonColors) {
  for (let item of kButtonColors) {
    let button = document.createElement('button');
    button.style.backgroundColor = item;
    button.addEventListener('click', function() {
      chrome.storage.sync.set({color: item}, function() {
        console.log('color is ' + item);
      })
    });
    page.appendChild(button);
  }
}
constructOptions(kButtonColors);*/
