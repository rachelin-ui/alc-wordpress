import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps({
    className: 'cta-banner',
  });

  return (
    <div {...blockProps}>
      <RichText
        tagName="h2"
        value={attributes.title}
        onChange={(value) => setAttributes({ title: value })}
        placeholder="Titre du CTA"
        aria-label="Titre du bloc CTA"
      />
      <RichText
        tagName="p"
        value={attributes.text}
        onChange={(value) => setAttributes({ text: value })}
        placeholder="Texte du CTA"
        aria-label="Texte du bloc CTA"
      />
      <a
        href={attributes.url}
        className="cta-button"
        role="button"
        tabIndex="0"
        aria-label="Lien du bouton CTA"
      >
        {attributes.buttonText || 'Cliquez ici'}
      </a>
    </div>
  );
}
