/**
 * AI Share Buttons Block - Editor Component
 */
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { Placeholder, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    const blockProps = useBlockProps();

    return (
        <div { ...blockProps }>
            <ServerSideRender
                block="kaais/share-buttons"
                EmptyResponsePlaceholder={ () => (
                    <Placeholder
                        icon="share-alt"
                        label={ __( 'AI Share Buttons', 'kaais' ) }
                    >
                        <p>{ __( 'No share buttons to display. Enable platforms in Settings → AI Share Buttons.', 'kaais' ) }</p>
                    </Placeholder>
                ) }
                LoadingResponsePlaceholder={ () => (
                    <Placeholder
                        icon="share-alt"
                        label={ __( 'AI Share Buttons', 'kaais' ) }
                    >
                        <Spinner />
                    </Placeholder>
                ) }
            />
        </div>
    );
}
