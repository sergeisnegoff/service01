'use strict';

//font
import "../css/fonts.css";

//bootstarp-grid
import "../libs/bootstrap/css/bootstrap4-grid.min.css";

//scrollbar
/* dock - https://github.com/mdbootstrap/perfect-scrollbar */
import PerfectScrollbar from 'perfect-scrollbar';
import '../../node_modules/perfect-scrollbar/css/perfect-scrollbar.css';

//swiper slider
import "../libs/swiper/swiper.css";
import Swiper from 'swiper/dist/js/swiper.js';

//imask for input and other
import IMask from 'imask';

//Hammer and dynamics for Swipers checkbox
import Hammer from 'hammerjs';
import dynamics from 'dynamics.js';

//choices.js
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

//FilePond
/* https://github.com/pqina/vue-filepond */
import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import ru_RU from 'filepond/locale/ru-ru.js';

//tippy
/* vue - https://kabbouchi.github.io/vue-tippy/4.0/ */
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css'; // optional for styling

//datapicker flatpickr
/* vue - https://github.com/ankurk91/vue-flatpickr-component */
import flatpickr from "flatpickr";
import 'flatpickr/dist/flatpickr.min.css'; // optional for styling
import { Russian } from "flatpickr/dist/l10n/ru.js"; // lang by rus

//popup sweetalert2
/* vue - https://github.com/avil13/vue-sweetalert2 */
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/dist/sweetalert2.min.css';

//autoComplete for search
import autoComplete from "@tarekraafat/autocomplete.js";

//base style
import uikitstyle from "../components/uikit/main.css";
import mainstyle from "../css/main.css";
import mediastyle from "../css/media.css";


global.sl = {
    /* init script */
    init: function() {
        this.initializePopup();
        this.initCustomScroll();
        this.initCustomTableToggle();
        this.initBtnHeaderToggle();
        this.initSlider();
        this.initTabsActive();
        this.initMaskInput();
        this.initSelect();
        this.initFileInput();
        this.initSwitchesInput();
        this.initTippy();
        this.initDatapicker();
        this.autoCompleteSearch();
    },
    /* initialize popup */
    initializePopup: function(){
        //initialize typing popup
        //mixin for popup dock https://sweetalert2.github.io/
        const typingPopup = Swal.mixin({
            position: 'center',
            showCancelButton: false,
            showConfirmButton: false,
            showCloseButton: true,
            heightAuto: false,
            focusConfirm: false,
            width: '490px',
            customClass: {
                cancelButton: 'btn__close-popup',
                htmlContainer: 'wrapper__popup'
            },
            didOpen: function() {
                global.sl.initMaskInput();
                //global.sl.initSelect();
            }
        });
        
        //base function popup
        let btnPopup = document.querySelectorAll("[data-btn-popup]");
        if(btnPopup) {
            btnPopup.forEach(function(item){
                if(item){
                    item.onclick = function() {
                        let btnName = this.getAttribute('data-btn-popup'),
                            divHtml = document.querySelectorAll("[data-popup="+btnName+"]")[0].cloneNode(true),
                            contentWrapper = divHtml.innerHTML;
                        
                        if(contentWrapper && (btnName === "organization")){
                            typingPopup.fire({
                                html: contentWrapper,
                                width: '1100px',
                                customClass: {
                                    container: 'organization__popup'
                                },
                            });
                            
                            return false;
                        }
                        
                        if(contentWrapper && (btnName === "filter" || btnName === "filter__invoice" || btnName === "filter__invoice-col")){
                            typingPopup.fire({
                                html: contentWrapper,
                                width: '435px',
                                position: 'top-end',
                                customClass: {
                                    cancelButton: 'btn__close-popupfilter',
                                    htmlContainer: 'wrapper__popup',
                                    container: 'filter__popup'
                                },
                            });
                            
                            return false;
                        }
                        
                        if(contentWrapper){
                            typingPopup.fire({
                                html: contentWrapper,
                            });
                        }
                        
                    } 
                }
            });
        }
        
        
    },
    initSlider: function () {
        
        if(document.querySelectorAll(".box__slider .swiper-container")){
        
            var galleryThumbs = new Swiper('.box__slider .gallery-thumbs', {
                spaceBetween: 15,
                slidesPerView: 3,
                freeMode: true,
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
            });
            var galleryTop = new Swiper('.box__slider .gallery-top', {
                spaceBetween: 15,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                thumbs: {
                    swiper: galleryThumbs
                }
            });
            
        }
        
    },
    /* init plugin scrollbar.js for div where do you need a scroll bar */
    initCustomScroll: function () {
        
        const container = document.querySelector("main");
        if(container){
            const ps__custom = new PerfectScrollbar(container, {
                wheelSpeed: 0.8,
                wheelPropagation: true,
                minScrollbarLength: 20
            }); 
        }
        
        const nav = document.querySelector(".wrapper__nav");
        if(nav) {
            const ps__nav = new PerfectScrollbar(nav, {
                wheelSpeed: 0.4,
                suppressScrollX: true,
                wheelPropagation: true,
                minScrollbarLength: 20
            });
        }
        
        const customScroll = document.querySelectorAll("[data-customScrollY]");
        if(customScroll) {
            customScroll.forEach(function(el) {
                const ps__nav = new PerfectScrollbar(el, {
                    wheelSpeed: 0.4,
                    suppressScrollX: true,
                    wheelPropagation: true,
                    minScrollbarLength: 20
                });
            });
        }
        
        const customScrollX = document.querySelectorAll("[data-customScrollX]");
        if(customScrollX) {
            customScrollX.forEach(function(el) {
                const ps__nav = new PerfectScrollbar(el, {
                    wheelSpeed: 0.4,
                    useBothWheelAxes: false,
                    minScrollbarLength: 20
                });
            });
        }
        
    },
    initCustomTableToggle: function() {
        let el = document.querySelectorAll("[data-table] .btn-toggle");
        if(el) {
            el.forEach(function(item){
                item.onclick = function() {
                    this.parentNode.classList.toggle('active');
                } 
            });
        }
    },
    /* this is govno-code/very bad tabs for active content(recomendation remove/del) */
    initTabsActive: function() {
        let tab = document.querySelectorAll(".box__tabs [data-tab]");
        if(tab) {
            tab.forEach(function(item){
                item.onclick = function(e) {
                    let name = e.target.getAttribute('data-tab');
                    let allelements = document.querySelectorAll("[data-tab]");
                    
                    allelements.forEach(function(el) {
                        el.classList.remove("active");
                    });
                    
                    let active = document.querySelectorAll("[data-tab="+name+"]");
                    
                    active.forEach(function(el) {
                        el.classList.add("active");
                    });
                } 
            });
        }
    },
    initBtnHeaderToggle: function() {
        let el = document.querySelectorAll(".btn__nav button")[0];
        if(el) {
            let header = document.querySelector("header");

            el.onclick = function() {
                header.classList.toggle('header-toggle');
            };
            
            header.onclick = function(event) {
                if(!header.classList.contains("header-toggle") && (event.target.tagName !== "SPAN" && event.target.tagName !== "A" && event.target.tagName !== "LI" && event.target.tagName !== "IMG" && event.target.tagName !== "BUTTON")){
                    header.classList.add('header-toggle');
                }
            };
            
        };        
        
    },
    initMaskInput: function() {
        let phone = document.querySelectorAll("[data-phone]");
        if(phone) {
            Array.prototype.forEach.call(phone, function (element) {
                var phoneMask = new IMask(element, {
                    mask: '+{7} (000) 000 00-00',
                    lazy: false, 
                    placeholderChar: '_'
                });
            });
        }
    },
    initSelect: function() {
        let select = document.querySelectorAll(".box__select select");
        
        if(select) {
            select.forEach(function(element) {
                const choices = new Choices(element, {
                    searchEnabled: false,
                    searchChoices: false,
                    shouldSort: false,
                    removeItemButton: true,
                    itemSelectText: 'Нажмите, чтобы выбрать',
                    locale: ru_RU,
                    callbackOnInit: function() {
                        let list__dropdown = this.dropdown.element;
                        const ps__nav = new PerfectScrollbar(list__dropdown, {
                            wheelSpeed: 0.4,
                            suppressScrollX: true,
                            wheelPropagation: true,
                            minScrollbarLength: 20
                        });
                    }
                });
            });
        }
    },
    initFileInput: function(){        
        FilePond.registerPlugin(FilePondPluginImagePreview);
        const inputElements = document.querySelectorAll('.box__input-file input');
        Array.from(inputElements).forEach(inputElement => {                
            FilePond.setOptions(ru_RU);
            FilePond.create(inputElement, {
                styleButtonRemoveItemPosition: "right",
                styleButtonProcessItemPosition: "left",
                styleLoadIndicatorPosition: "left",
                styleProgressIndicatorPosition: "left",
            });
        });
    },
    initSwitchesInput: function(){
        let switchArr = document.querySelectorAll(".box__switch");
        if(switchArr) {
            Array.prototype.forEach.call(switchArr, function (elSwitch) {  
                if(elSwitch.querySelector("input").disabled == false){
                    const input = elSwitch.querySelector("input");
                    const box = elSwitch.querySelector("input + span");
                    const wrapper = elSwitch.querySelector("input + span span.box__radiobox-icon");
                    const hBox = new Hammer(box);
                    hBox.add(new Hammer.Pan({
                        threshold: 0
                    }));
                    const threshold = 0;
                    const wrapperWidth = parseInt(getComputedStyle(box).getPropertyValue("--width-bar"));
                    const wrapperHeight = parseInt(getComputedStyle(box).getPropertyValue("--height-bar"));

                    let newX = {
                        deltaX: wrapperWidth / 3
                    };
                    
                    let point2 = wrapperWidth - wrapperHeight;
                    
                    const physicspoint = {
                        deltaX: 0
                    };
                    
                    const physicspoint2 = {
                        deltaX: point2
                    };
                    
                    let currentPoint = physicspoint;

                    const whichPoint = function (point, p1, p2) {
                        if (newX.deltaX >= (physicspoint.deltaX + physicspoint2.deltaX) / 2) {
                            currentPoint = physicspoint2;
                        } else {
                            currentPoint = physicspoint;
                        }
                    }
                    
                    const inner = function (a) {
                        return (a / point2 * wrapperHeight / 2);
                    }

                    const springBack = function () {
                        dynamics.animate(newX, currentPoint, {
                            //complete: console.log("complete"),
                            change: e => {
                                //console.log(e);
                                box.style.setProperty("--horizontal", e.deltaX);
                                wrapper.style.setProperty("--inner", inner(e.deltaX));
                            },
                            type: dynamics.spring
                        });
                    };
                    
                    hBox.on("pan", function (e) {
                        newX.deltaX = e.deltaX + currentPoint.deltaX;

                        if (newX.deltaX > point2 + (point2 * threshold)) {
                            newX.deltaX = point2 + (point2 * threshold);
                        }
                        if (newX.deltaX < 0 - (point2 * threshold)) {
                            newX.deltaX = 0 - (point2 * threshold);
                        }
                        box.style.setProperty("--horizontal", newX.deltaX);
                        wrapper.style.setProperty("--inner", inner(newX.deltaX));
                    });

                    hBox.on("panend", function (e) {
                        
                        if (newX.deltaX == 0) {
                            input.checked = false;
                        } else {
                            input.checked = true;
                        }
                        
                        whichPoint();
                        springBack();
                    });

                    hBox.on("tap", function (e) {
                        
                        if (input.checked == true) {
                            newX.deltaX = 0;
                        } else {
                            newX.deltaX = 25;
                        }

                        box.style.setProperty("--horizontal", newX.deltaX);
                        wrapper.style.setProperty("--inner", inner(newX.deltaX));

                        whichPoint();
                        springBack();
                    });
                    
                }
            });                            
        }
    },
    initTippy: function(){
        let tippyEl = document.querySelectorAll("[data-tippy-content]");
        if(tippyEl) {
            Array.prototype.forEach.call(tippyEl, function (el) {  
                tippy(el, {
                    trigger: 'mouseenter focus',
                });
            });
        }                                 
    },
    initDatapicker: function(){
        let datapicker = document.querySelectorAll("[data-datapicker]");
        if(datapicker) {
            Array.prototype.forEach.call(datapicker, function (element) {
                flatpickr(element, {
                    "locale": Russian,
                    dateFormat: 'd-m-Y', 
                    //config
                });
            });
        }
    },
    autoCompleteSearch: function() {
        let inputs = document.querySelectorAll("[data-autocomplete]");
        if(inputs) {
            inputs.forEach(function(Element) {
                const autoCompleteJS = new autoComplete({
                    selector: () => {
                        return Element; // Any valid selector
                    },
                    placeHolder: "Наименование товара",
                    data: {
                        //for examples
                        src: [
                            "Сыр 'Велий' с пажитником", 
                            "Сыр 'Велий' с томатами", 
                            "Сыр 'Амстеллер' выдержанный",
                            "Сыр 'Большеволжский' с пажитником",
                            "Сыр 'Белпер кнолле' с прованскими травами из козьего молока"
                        ],
                        cache: true,
                    },
                    resultsList: {
                        element: (list, data) => {
                            if (!data.results.length) {
                                // Create "No Results" message element
                                const message = document.createElement("div");
                                // Add class to the created element
                                message.setAttribute("class", "no_result");
                                // Add message text content
                                message.innerHTML = `<span>Такого наименование товара нет</span>`;
                                // Append message element to the results list
                                list.prepend(message);
                            }
                        },
                        noResults: true,
                    },
                    resultItem: {
                        highlight: {
                            render: true
                        }
                    },
                    events: {
                        input: {
                            selection: (event) => {
                                const selection = event.detail.selection.value;
                                autoCompleteJS.input.value = selection;
                                console.log(event);
                            }
                        }
                    }
                });
                
                let list = autoCompleteJS.list;
                const ps__nav = new PerfectScrollbar(list, {
                    wheelSpeed: 0.4,
                    suppressScrollX: true,
                    wheelPropagation: true,
                    minScrollbarLength: 20
                });
            });
        }
    }
}

global.sl.init();