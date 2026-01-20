@if (!(auth()->user()?->hasRole('super_admin') ?? false))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // نبحث عن أي عنصر يحتوي النص App\Models\Tenant (subtitle تحت Company)
            const nodes = Array.from(document.querySelectorAll('*'));
            const target = nodes.find(el =>
                el &&
                el.childElementCount === 0 &&
                typeof el.textContent === 'string' &&
                el.textContent.trim() === 'App\\Models\\Tenant'
            );

            if (!target) return;

            // نطلع لأقرب section/card ونخفيه
            const wrapper =
                target.closest('.fi-section') ||
                target.closest('.fi-card') ||
                target.closest('section') ||
                target.closest('div');

            if (wrapper) {
                wrapper.style.display = 'none';
            }
        });
    </script>
@endif
