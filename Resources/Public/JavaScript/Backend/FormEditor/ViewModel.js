/**
 * Module: TYPO3/CMS/FormElementLinkedCheckbox/Backend/FormEditor/ViewModel
 */

define(['jquery',
        'TYPO3/CMS/Form/Backend/FormEditor/StageComponent'
        ], function ($, StageComponent) {
        'use strict';

    return (function ($, StageComponent) {

        /**
         * @private
         *
         * @var object
         */
        var _formEditorApp = null;

        /**
         * @private
         *
         * @return object
         */
        function getFormEditorApp() {
            return _formEditorApp;
        }

        /**
         * @private
         *
         * @return object
         */
        function getPublisherSubscriber() {
            return getFormEditorApp().getPublisherSubscriber();
        }

        /**
         * @private
         *
         * @return void
         */
        function _subscribeEvents() {

            /**
             * @private
             *
             * @param string
             * @param array
             *              args[0] = formElement
             *              args[1] = template
             * @return void
             * @subscribe view/stage/abstract/render/template/perform
             */
            getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform', function (topic, args) {
                if (args[0].get('type') === 'LinkedCheckbox') {
                    StageComponent.renderCheckboxTemplate(args[0], args[1]);
                }
            });
        }

        /**
         * @public
         *
         * @param object formEditorApp
         * @return void
         */
        function bootstrap(formEditorApp) {
            _formEditorApp = formEditorApp;
            _subscribeEvents();
        }

        /**
         * Publish the public methods.
         * Implements the "Revealing Module Pattern".
         */
        return {
            bootstrap: bootstrap
        };

    })($, StageComponent);
});
