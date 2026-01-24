/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/block-editor/src/components/editor.js"
/*!**************************************************!*\
  !*** ./js/block-editor/src/components/editor.js ***!
  \**************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* export default binding */ __WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_format_library__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/format-library */ "@wordpress/format-library");
/* harmony import */ var _wordpress_format_library__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_format_library__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/drawer-right.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/fullscreen.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/redo.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/undo.mjs");
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ../functions */ "./js/block-editor/src/functions.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__);










/* harmony default export */ function __WEBPACK_DEFAULT_EXPORT__({
  textarea
}) {
  const {
    value,
    setValue,
    hasUndo,
    hasRedo,
    undo,
    redo
  } = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_3__.useStateWithHistory)({
    blocks: (0,_functions__WEBPACK_IMPORTED_MODULE_11__.parseContent)(textarea.value)
  });
  const [isSidebarOpen, toggleSidebar] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useReducer)(state => !state, false);
  const [isFullscreen, toggleFullscreen] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useReducer)(state => !state, false);
  const persistBlocks = blocks => {
    setValue({
      blocks
    });
    textarea.value = (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.serialize)(blocks);
  };
  const inserterProps = {
    size: 'compact',
    variant: 'primary'
  };
  const settings = JSON.parse(textarea.dataset.settings);
  const editorSettings = (0,_functions__WEBPACK_IMPORTED_MODULE_11__.getEditorSettings)(settings);

  // Disable body scroll in fullscreen
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    document.body.style.overflow = isFullscreen ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [isFullscreen]);
  const editor = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.BlockEditorProvider, {
    value: value.blocks,
    onChange: persistBlocks,
    settings: editorSettings,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
      justify: "space-between",
      className: "rwmb-block-editor__toolbar",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
        justify: "flex-start",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.Inserter, {
          toggleProps: inserterProps
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          onClick: undo,
          disabled: !hasUndo,
          accessibleWhenDisabled: true,
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__["default"],
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Undo', 'meta-box'),
          size: "compact"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          onClick: redo,
          disabled: !hasRedo,
          accessibleWhenDisabled: true,
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__["default"],
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Redo', 'meta-box'),
          size: "compact"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.BlockNavigationDropdown, {})]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
        justify: "flex-end",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__["default"],
          "aria-pressed": isFullscreen,
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Toggle Fullscreen', 'meta-box'),
          size: "compact",
          onClick: toggleFullscreen
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__["default"],
          "aria-pressed": isSidebarOpen,
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__.__)('Toggle Sidebar', 'meta-box'),
          size: "compact",
          onClick: toggleSidebar
        })]
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
      gap: 0,
      align: "stretch",
      className: "rwmb-block-editor__main",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("div", {
        className: "rwmb-block-editor__content editor-styles-wrapper",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.BlockCanvas, {
          height: settings.height,
          styles: editorSettings.styles
        })
      }), isSidebarOpen && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("div", {
        className: "rwmb-block-editor__sidebar",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.BlockInspector, {})
      })]
    })]
  });
  if (isFullscreen) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.createPortal)(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("div", {
      className: "rwmb-block-editor__canvas rwmb-block-editor__canvas--fullscreen",
      children: editor
    }), (0,_functions__WEBPACK_IMPORTED_MODULE_11__.getPortalRoot)());
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("div", {
    className: "rwmb-block-editor__canvas",
    children: editor
  });
}

/***/ },

/***/ "./js/block-editor/src/functions.js"
/*!******************************************!*\
  !*** ./js/block-editor/src/functions.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getEditorSettings: () => (/* binding */ getEditorSettings),
/* harmony export */   getPortalRoot: () => (/* binding */ getPortalRoot),
/* harmony export */   parseContent: () => (/* binding */ parseContent)
/* harmony export */ });
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_format_library__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/format-library */ "@wordpress/format-library");
/* harmony import */ var _wordpress_format_library__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_format_library__WEBPACK_IMPORTED_MODULE_3__);




const parseContent = content => content.includes('<!--') ? (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.parse)(content) : (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.rawHandler)({
  HTML: content
});
const getPortalRoot = () => {
  let el = document.getElementById('rwmb-block-editor-portal');
  if (!el) {
    el = document.createElement('div');
    el.id = 'rwmb-block-editor-portal';
    document.body.appendChild(el);
  }
  return el;
};
const getEditorSettings = ({
  allowed_blocks
}) => (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(select => {
  const settings = select(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.store).getSettings();
  if (Array.isArray(allowed_blocks) && allowed_blocks.length > 0) {
    settings.allowedBlockTypes = allowed_blocks;
  }
  return settings;
});

/***/ },

/***/ "./js/block-editor/src/index.js"
/*!**************************************!*\
  !*** ./js/block-editor/src/index.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_block_library__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-library */ "@wordpress/block-library");
/* harmony import */ var _wordpress_block_library__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_library__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/editor */ "./js/block-editor/src/components/editor.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./style.scss */ "./js/block-editor/src/style.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__);





// Make sure to load stylesheets:
// import '@wordpress/block-editor/build-style/style.css'; // wp-block-editor
// import '@wordpress/components/build-style/style.css'; // wp-components

// Make sure to load stylesheets:
// import '@wordpress/block-library/build-style/editor.css'; // wp-edit-blocks
// import '@wordpress/block-library/build-style/style.css'; // wp-block-library
// import '@wordpress/block-library/build-style/theme.css'; // wp-block-library-theme

(0,_wordpress_block_library__WEBPACK_IMPORTED_MODULE_0__.registerCoreBlocks)();

/**
 * Attach IsolatedBlockEditor to a textarea
 *
 * @param {HTMLTextAreaElement} textarea Textarea node
 * @param {BlockEditorSettings} userSettings Settings object
 */
function attachEditor(textarea) {
  // Create a node after the textarea
  const editor = document.createElement('div');
  editor.classList.add('rwmb-block-editor');
  const root = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createRoot)(editor);

  // Insert after the textarea, and hide it
  textarea.parentNode.insertBefore(editor, textarea.nextSibling);
  textarea.style.display = 'none';
  root.render(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_components_editor__WEBPACK_IMPORTED_MODULE_2__["default"], {
    textarea: textarea
  }));
}
const transform = textarea => {
  // Remove the clone editor from the DOM if it exists
  const editor = textarea.nextElementSibling;
  if (editor && editor.classList.contains('rwmb-block-editor')) {
    editor.remove();
  }
  attachEditor(textarea);
};
const init = () => {
  document.querySelectorAll('.rwmb-block_editor2-wrapper textarea').forEach(transform);
};
rwmb.$document.on('mb_ready', init).on('clone', '.rwmb-block_editor2', function () {
  // Transform a textarea to an editor is a heavy task.
  // Moving it to the end of task queue with setTimeout makes cloning faster.
  setTimeout(() => transform(this), 200);
});

/***/ },

/***/ "./js/block-editor/src/style.scss"
/*!****************************************!*\
  !*** ./js/block-editor/src/style.scss ***!
  \****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/drawer-right.mjs"
/*!*************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/drawer-right.mjs ***!
  \*************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ drawer_right_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/drawer-right.tsx


var drawer_right_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(
  _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path,
  {
    fillRule: "evenodd",
    clipRule: "evenodd",
    d: "M18 4H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-4 14.5H6c-.3 0-.5-.2-.5-.5V6c0-.3.2-.5.5-.5h8v13zm4.5-.5c0 .3-.2.5-.5.5h-2.5v-13H18c.3 0 .5.2.5.5v12z"
  }
) });

//# sourceMappingURL=drawer-right.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/fullscreen.mjs"
/*!***********************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/fullscreen.mjs ***!
  \***********************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ fullscreen_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/fullscreen.tsx


var fullscreen_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M6 4a2 2 0 0 0-2 2v3h1.5V6a.5.5 0 0 1 .5-.5h3V4H6Zm3 14.5H6a.5.5 0 0 1-.5-.5v-3H4v3a2 2 0 0 0 2 2h3v-1.5Zm6 1.5v-1.5h3a.5.5 0 0 0 .5-.5v-3H20v3a2 2 0 0 1-2 2h-3Zm3-16a2 2 0 0 1 2 2v3h-1.5V6a.5.5 0 0 0-.5-.5h-3V4h3Z" }) });

//# sourceMappingURL=fullscreen.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/redo.mjs"
/*!*****************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/redo.mjs ***!
  \*****************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ redo_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/redo.tsx


var redo_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M15.6 6.5l-1.1 1 2.9 3.3H8c-.9 0-1.7.3-2.3.9-1.4 1.5-1.4 4.2-1.4 5.6v.2h1.5v-.3c0-1.1 0-3.5 1-4.5.3-.3.7-.5 1.3-.5h9.2L14.5 15l1.1 1.1 4.6-4.6-4.6-5z" }) });

//# sourceMappingURL=redo.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/undo.mjs"
/*!*****************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@11.5.0_react@18.3.1/node_modules/@wordpress/icons/build-module/library/undo.mjs ***!
  \*****************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ undo_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/undo.tsx


var undo_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M18.3 11.7c-.6-.6-1.4-.9-2.3-.9H6.7l2.9-3.3-1.1-1-4.5 5L8.5 16l1-1-2.7-2.7H16c.5 0 .9.2 1.3.5 1 1 1 3.4 1 4.5v.3h1.5v-.2c0-1.5 0-4.3-1.5-5.7z" }) });

//# sourceMappingURL=undo.mjs.map


/***/ },

/***/ "@wordpress/block-editor"
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
(module) {

module.exports = window["wp"]["blockEditor"];

/***/ },

/***/ "@wordpress/block-library"
/*!**************************************!*\
  !*** external ["wp","blockLibrary"] ***!
  \**************************************/
(module) {

module.exports = window["wp"]["blockLibrary"];

/***/ },

/***/ "@wordpress/blocks"
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
(module) {

module.exports = window["wp"]["blocks"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/compose"
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["compose"];

/***/ },

/***/ "@wordpress/data"
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["data"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/format-library"
/*!***************************************!*\
  !*** external ["wp","formatLibrary"] ***!
  \***************************************/
(module) {

module.exports = window["wp"]["formatLibrary"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "@wordpress/primitives"
/*!************************************!*\
  !*** external ["wp","primitives"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["primitives"];

/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"block-editor": 0,
/******/ 			"./style-block-editor": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunk"] = globalThis["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-block-editor"], () => (__webpack_require__("./js/block-editor/src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=block-editor.js.map