<?php

class ModalService
{
    public static function render(): string
    {
        return '
        <div id="app-modal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal()">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 id="modal-title"></h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div id="modal-body" class="modal-body"></div>
                <div id="modal-footer" class="modal-footer"></div>
            </div>
        </div>';
    }
}
