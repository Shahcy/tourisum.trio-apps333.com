<script>
    document.addEventListener('DOMContentLoaded', () => {
        try {
            if (window.Livewire && typeof window.Livewire.start === 'function') {
                window.Livewire.start();
            }
        } catch (error) {
            // Ignore double-start errors.
        }
    });
</script>