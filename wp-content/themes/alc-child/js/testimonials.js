document.getElementById('load-more-testimonials').addEventListener('click', function () {
    const container = document.getElementById('alc-testimonials');
    const currentCount = container.children.length;

    fetch(`/wp-admin/admin-ajax.php?action=load_more_testimonials&offset=${currentCount}`)
        .then(res => res.text())
        .then(html => container.insertAdjacentHTML('beforeend', html));
});
