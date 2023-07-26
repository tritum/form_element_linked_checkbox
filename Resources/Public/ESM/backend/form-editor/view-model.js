/**
 * Module: @tritum/form-element-linked-checkbox/backend/form-editor/view-model.js
 */

function subscribeEvents(formEditorApp) {
    formEditorApp.getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform',(
        topic,
        [formElement, template]
    ) => {
        if (formElement.get('type') === 'LinkedCheckbox') {
            formEditor.getViewModel().getStage().renderCheckboxTemplate(formElement, template);
        }
    });
}

export function bootstrap(formEditorApp) {
    subscribeEvents(formEditorApp);
}
