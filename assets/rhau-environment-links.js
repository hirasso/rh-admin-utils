(()=>{"use strict";var s=Object.defineProperty,r=(n,t,e)=>t in n?s(n,t,{enumerable:!0,configurable:!0,writable:!0,value:e}):n[t]=e,i=(n,t,e)=>r(n,typeof t!="symbol"?t+"":t,e);const u=window.jQuery;class o extends HTMLElement{constructor(){super(),i(this,"onKeyDown",t=>{switch(t.code){case"Space":this.handleSpaceDown(t);break;case"Escape":this.handleEscapeDown(t);break}}),i(this,"onFocusIn",t=>{if(this.isVisible()&&(t.stopPropagation(),!t.target.matches("rhau-environment-link"))){if(t.target.matches(":first-child"))return this.focusLastLink();this.focusFirstLink()}}),i(this,"onClick",t=>{if(t.target.matches("rhau-environment-link"))return this.openEnvironmentLink(t.target);t.target.closest("rhau-environment-links")||this.isVisible()&&(t.stopPropagation(),this.hide())}),i(this,"handleSpaceDown",t=>{if(!(this.isInputElement(t.target)||this.isSpecialKeyDown(t))){if(t.preventDefault(),t.stopPropagation(),t.target.matches("rhau-environment-link"))return this.openEnvironmentLink(t.target);this.show()}}),this.attachShadow({mode:"open",delegatesFocus:!0}),this.shadowRoot.innerHTML="<slot>"}connectedCallback(){document.addEventListener("focusin",this.onFocusIn,{capture:!0}),document.addEventListener("keydown",this.onKeyDown,{capture:!0}),document.addEventListener("click",this.onClick,{capture:!0})}disconnectedCallback(){document.removeEventListener("focusin",this.onFocusIn),document.removeEventListener("keydown",this.onKeyDown),document.removeEventListener("click",this.onClick)}isVisible(){return this.classList.contains("is-visible")}isInputElement(t){if(t!=null&&t.closest("rhau-environment-link"))return!1;const e=t==null?void 0:t.getAttribute("tabindex");return e!==null?!isNaN(parseInt(e))&&parseInt(e)>=0:t==null?void 0:t.matches('button, input, textarea, select, a, [contenteditable="true"]')}isSpecialKeyDown(t){return t.metaKey||t.ctrlKey||t.shiftKey||t.altKey}show(){this.isVisible()||(this.classList.add("is-visible"),this.focusFirstLink())}focusFirstLink(){this.querySelector("rhau-environment-link:first-of-type").focus()}focusLastLink(){this.querySelector("rhau-environment-link:last-of-type").focus()}handleEscapeDown(t){this.isVisible()&&(t.preventDefault(),t.stopPropagation(),this.hide())}hide(){this.classList.remove("is-visible")}openEnvironmentLink(t){if(!this.isVisible())return;const e=new URL(window.location.href),a=e.pathname+e.search+e.hash,c=new URL(t.getAttribute("data-remote-root"));window.open(c.origin+a),this.hide()}trapFocus(t){t.target.matches("rhau-environment-link:last-of-type")||(t.preventDefault(),t.stopPropagation())}}customElements.define("rhau-environment-links",o)})();
