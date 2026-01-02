import Chart from 'chart.js/auto';

// Make Chart available globally for Livewire components
window.Chart = Chart;

// Import FilePond and plugins
import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

// Register FilePond plugins
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// Import FilePond CSS
import 'filepond/dist/filepond.min.css';

// Make FilePond available globally for Livewire FilePond component
window.LivewireFilePond = FilePond;

// Import Tiptap Editor and extensions
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import Youtube from '@tiptap/extension-youtube';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableCell } from '@tiptap/extension-table-cell';
import TextAlign from '@tiptap/extension-text-align';
import Blockquote from '@tiptap/extension-blockquote';

// Register Tiptap as Alpine.js data component
document.addEventListener('alpine:init', () => {
    Alpine.data('tiptapEditor', (content) => {
        let editor;

        return {
            content: content,
            updatedAt: Date.now(),

            init() {
                const _this = this;

                this.$nextTick(() => {
                    const element = this.$refs.editor;
                    if (!element) {
                        return;
                    }

                    editor = new Editor({
                        element: element,
                        content: this.content,
                        extensions: [
                            StarterKit.configure({
                                heading: {
                                    levels: [1, 2, 3],
                                },
                                // Disable blockquote from StarterKit to use the standalone one
                                blockquote: false,
                            }),
                            Link.configure({
                                openOnClick: false,
                            }),
                            Image.configure({
                                inline: false,
                                allowBase64: true,
                            }),
                            Youtube.configure({
                                controls: true,
                                nocookie: true,
                            }),
                            Table.configure({
                                resizable: true,
                            }),
                            TableRow,
                            TableHeader,
                            TableCell,
                            TextAlign.configure({
                                types: ['heading', 'paragraph'],
                            }),
                            Blockquote,
                            Placeholder.configure({
                                placeholder: 'Escribe tu contenido aquí...',
                            }),
                        ],
                        onCreate() {
                            _this.updatedAt = Date.now();
                        },
                        onUpdate() {
                            _this.content = editor.getHTML();
                            _this.updatedAt = Date.now();
                        },
                        onSelectionUpdate() {
                            _this.updatedAt = Date.now();
                        },
                        editorProps: {
                            attributes: {
                                class: 'focus:outline-none',
                            },
                        },
                    });

                    // Watch for content changes from Livewire
                    this.$watch('content', (newContent) => {
                        if (!editor || editor.isDestroyed) {
                            return;
                        }
                        
                        const currentContent = editor.getHTML();
                        if (newContent !== currentContent && newContent !== undefined && newContent !== null) {
                            editor.commands.setContent(newContent || '', false);
                        }
                    });
                });
            },

            isLoaded() {
                return editor !== undefined && editor !== null;
            },

            isActive(type, updatedAt, opts = {}) {
                if (!editor || editor.isDestroyed) {
                    return false;
                }
                // Use updatedAt to force reactivity (Alpine needs it to track changes)
                // Handle different call patterns
                if (typeof updatedAt === 'number' && typeof opts === 'object' && Object.keys(opts).length > 0) {
                    // Pattern: isActive('heading', { level: 1 }, updatedAt)
                    return editor.isActive(type, opts);
                } else if (typeof updatedAt === 'string' && typeof opts === 'number') {
                    // Pattern: isActive('textAlign', 'left', updatedAt)
                    return editor.isActive({ textAlign: updatedAt });
                } else if (typeof opts === 'number') {
                    // Pattern: isActive('bold', updatedAt)
                    return editor.isActive(type);
                } else if (typeof updatedAt === 'object') {
                    // Pattern: isActive('heading', { level: 1 })
                    return editor.isActive(type, updatedAt);
                }
                // Default: isActive('bold')
                return editor.isActive(type);
            },

            toggleBold() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleBold().run();
                }
            },

            toggleItalic() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleItalic().run();
                }
            },

            toggleStrike() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleStrike().run();
                }
            },

            toggleHeading(level) {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleHeading({ level }).run();
                }
            },

            toggleBulletList() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleBulletList().run();
                }
            },

            toggleOrderedList() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleOrderedList().run();
                }
            },

            setLink() {
                if (!editor || editor.isDestroyed) {
                    return;
                }
                const url = window.prompt('URL del enlace');
                if (url) {
                    editor.chain().focus().setLink({ href: url }).run();
                }
            },

            unsetLink() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().unsetLink().run();
                }
            },

            undo() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().undo().run();
                }
            },

            redo() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().redo().run();
                }
            },

            canUndo() {
                if (!editor || editor.isDestroyed) {
                    return false;
                }
                return editor.can().undo();
            },

            canRedo() {
                if (!editor || editor.isDestroyed) {
                    return false;
                }
                return editor.can().redo();
            },

            // Image methods
            setImage() {
                if (!editor || editor.isDestroyed) {
                    return;
                }
                const url = window.prompt('URL de la imagen');
                if (url) {
                    editor.chain().focus().setImage({ src: url }).run();
                }
            },

            // YouTube methods
            setYoutubeVideo() {
                if (!editor || editor.isDestroyed) {
                    return;
                }
                const url = window.prompt('URL del vídeo de YouTube (ej: https://www.youtube.com/watch?v=...)');
                if (url) {
                    editor.chain().focus().setYoutubeVideo({ src: url }).run();
                }
            },

            // Table methods
            insertTable() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                }
            },

            addColumnBefore() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().addColumnBefore().run();
                }
            },

            addColumnAfter() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().addColumnAfter().run();
                }
            },

            deleteColumn() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().deleteColumn().run();
                }
            },

            addRowBefore() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().addRowBefore().run();
                }
            },

            addRowAfter() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().addRowAfter().run();
                }
            },

            deleteRow() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().deleteRow().run();
                }
            },

            deleteTable() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().deleteTable().run();
                }
            },

            mergeCells() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().mergeCells().run();
                }
            },

            splitCell() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().splitCell().run();
                }
            },

            toggleHeaderColumn() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleHeaderColumn().run();
                }
            },

            toggleHeaderRow() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleHeaderRow().run();
                }
            },

            toggleHeaderCell() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleHeaderCell().run();
                }
            },

            // Blockquote
            toggleBlockquote() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().toggleBlockquote().run();
                }
            },

            // Horizontal Rule
            setHorizontalRule() {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().setHorizontalRule().run();
                }
            },

            // Text Align
            setTextAlign(align) {
                if (editor && !editor.isDestroyed) {
                    editor.chain().focus().setTextAlign(align).run();
                }
            },
        };
    });
});

