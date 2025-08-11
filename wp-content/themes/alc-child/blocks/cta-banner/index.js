import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, URLInput } from '@wordpress/block-editor';
import { PanelBody, ColorPalette } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

registerBlockType('alc/cta-banner', {
  edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();

    return (
      <>
        <InspectorControls>
          <PanelBody title="Couleur de fond">
            <ColorPalette
              value={attributes.backgroundColor}
              onChange={(color) => setAttributes({ backgroundColor: color })}
            />
          </PanelBody>
        </InspectorControls>
        <div {...blockProps} style={{ backgroundColor: attributes.backgroundColor, padding: '20px', color: '#fff' }}>
          <RichText
            tagName="h2"
            value={attributes.title}
            onChange={(title) => setAttributes({ title })}
            placeholder="Titre..."
          />
          <RichText
            tagName="p"
            value={attributes.text}
            onChange={(text) => setAttributes({ text })}
            placeholder="Texte..."
          />
          <URLInput
            value={attributes.url}
            onChange={(url) => setAttributes({ url })}
          />
        </div>
      </>
    );
  },
  save({ attributes }) {
    const blockProps = useBlockProps.save();
    return (
      <div {...blockProps} style={{ backgroundColor: attributes.backgroundColor, padding: '20px', color: '#fff' }}>
        <h2>{attributes.title}</h2>
        <p>{attributes.text}</p>
        <a href={attributes.url} style={{ color: '#fff', textDecoration: 'underline' }}>En savoir plus</a>
      </div>
    );
  }
});
