import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const blockProps = useBlockProps.save({
    className: 'cta-banner',
  });

  return (
    <div {...blockProps}>
      <RichText.Content tagName="h2" value={attributes.title} />
      <RichText.Content tagName="p" value={attributes.text} />
      <a
        href={attributes.url}
        className="cta-button"
        role="button"
        tabIndex="0"
        aria-label="Lien du bouton CTA"
      >
        {attributes.buttonText}
      </a>
    </div>
  );
}
