parcelRequire=function(e){var r="function"==typeof parcelRequire&&parcelRequire,n="function"==typeof require&&require,i={};function u(e,u){if(e in i)return i[e];var t="function"==typeof parcelRequire&&parcelRequire;if(!u&&t)return t(e,!0);if(r)return r(e,!0);if(n&&"string"==typeof e)return n(e);var o=new Error("Cannot find module '"+e+"'");throw o.code="MODULE_NOT_FOUND",o}return u.register=function(e,r){i[e]=r},i=e(u),u.modules=i,u}(function (require) {var c=this;var a={};var d=$=c.jQuery;class b{constructor(){d(document).ready(()=>this.onDocReady()),$("form#post").one("submit",e=>this.beforeSubmitPostForm(e))}onDocReady(){this.enableSubmitDiv(),this.initAdminBarButtons(),this.reopenSavedAcfFieldObjects(),this.restoreScrollTop(),this.removeFromStore("scrollTop")}beforeSubmitPostForm(e){this.saveOpenAcfFieldObjects(),this.addToStore("scrollTop",$(document).scrollTop())}restoreScrollTop(){var e=this.getFromStore("scrollTop");e&&$(document).scrollTop(parseInt(e))}initAdminBarButtons(){let e=$("#submitdiv input[id=\"publish\"]:visible"),t=$("#wp-admin-bar-rh-publish a");e.length&&t.text(e.val()).click(o=>{o.preventDefault(),e.click(),t.addClass("is-disabled")});let o=$("#submitdiv input[id=\"save-post\"]:visible"),s=$("#wp-admin-bar-rh-save a");o.length&&s.text(o.val()).click(e=>{e.preventDefault(),o.click(),s.addClass("is-disabled")}),"undefined"!=typeof acf&&void 0!==acf.addFilter&&acf.addFilter("validation_complete",(e,o)=>(e.errors&&(t.length&&t.removeClass("is-disabled"),s.length&&s.removeClass("is-disabled")),e))}enableSubmitDiv(){$("#submitdiv").addClass("rhau-enabled")}addToStore(e,t){sessionStorage.setItem(this.getStorageKey(e),JSON.stringify(t))}removeFromStore(e){sessionStorage.removeItem(this.getStorageKey(e))}getFromStore(e){let t=sessionStorage.getItem(this.getStorageKey(e));return t?JSON.parse(t):t}saveOpenAcfFieldObjects(){if("undefined"==typeof acf)return;if("function"!=typeof acf.getFieldObjects)return;let e=[];try{for(const t of acf.getFieldObjects())t.isOpen()&&(t.close=()=>{},e.push(t.getKey()))}catch(t){console.warn(t)}this.addToStore("open-acf-field-objects",e)}getNested(e,...t){return t.reduce((e,t)=>e&&e[t],e)}reopenSavedAcfFieldObjects(){if("undefined"==typeof acf)return;if("function"!=typeof acf.getFieldObjects)return;let e=this.getFromStore("open-acf-field-objects");if(e){try{for(const t of e){const e=acf.getFieldObject(t),o=e.$el.children(".settings");e.$el.addClass("open"),o.css({display:"block"}),acf.doAction("open_field_object",e),e.trigger("openFieldObject"),acf.doAction("show",o)}}catch(t){console.warn(t)}this.removeFromStore("open-acf-field-objects")}}getStorageKey(e){return e+":"+window.location.pathname.replace(/^\/+/g,"").split("/").join("-")}}a.default=b,new b;if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=a}else if(typeof define==="function"&&define.amd){define(function(){return a})}a.__esModule=true;return{"oIgb":a};});