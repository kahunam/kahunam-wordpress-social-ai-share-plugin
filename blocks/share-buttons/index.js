/**
 * AI Share Buttons Block
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

import './editor.css';

registerBlockType( metadata.name, {
    edit: Edit,
    save: () => null, // Dynamic block - PHP handles rendering
} );
