<!-- Image Modal Component -->
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm">
    <div class="relative max-w-7xl max-h-[90vh] p-4">
        <!-- Close Button -->
        <button
            onclick="closeImageModal()"
            class="absolute -top-4 -right-4 z-10 bg-white rounded-full p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors shadow-lg"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Modal Image -->
        <img
            id="modalImage"
            src=""
            alt="Vehicle Image"
            class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
        >

        <!-- Navigation Buttons (for gallery navigation) -->
        <button
            id="prevButton"
            onclick="navigateImage(-1)"
            class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 rounded-full p-3 text-gray-800 hover:bg-opacity-100 transition-all shadow-lg hidden"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <button
            id="nextButton"
            onclick="navigateImage(1)"
            class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 rounded-full p-3 text-gray-800 hover:bg-opacity-100 transition-all shadow-lg hidden"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

        <!-- Image Counter -->
        <div id="imageCounter" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-60 text-white px-4 py-2 rounded-full text-sm hidden">
            <span id="currentImage">1</span> / <span id="totalImages">1</span>
        </div>
    </div>
</div>

<script>
    let currentImageIndex = 0;
    let allImages = [];

    function openImageModal(imageSrc) {
        // Collect all images on the page for navigation
        const featuredImg = document.querySelector('[data-testid="infolist.featured_image.container"] img');
        const galleryImgs = document.querySelectorAll('[data-testid="infolist.gallery_images.container"] img');

        allImages = [];

        // Add featured image first
        if (featuredImg && featuredImg.src) {
            allImages.push(featuredImg.src);
        }

        // Add gallery images
        galleryImgs.forEach(img => {
            if (img.src && img.src !== featuredImg?.src) {
                allImages.push(img.src);
            }
        });

        // Find current image index
        currentImageIndex = allImages.findIndex(src => src === imageSrc);
        if (currentImageIndex === -1) currentImageIndex = 0;

        // Show modal
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        modalImage.src = imageSrc;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Update navigation
        updateNavigation();

        // Add keyboard navigation
        document.addEventListener('keydown', handleKeyPress);
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Remove keyboard navigation
        document.removeEventListener('keydown', handleKeyPress);
    }

    function navigateImage(direction) {
        if (allImages.length <= 1) return;

        currentImageIndex += direction;

        // Loop around
        if (currentImageIndex >= allImages.length) {
            currentImageIndex = 0;
        } else if (currentImageIndex < 0) {
            currentImageIndex = allImages.length - 1;
        }

        const modalImage = document.getElementById('modalImage');
        modalImage.src = allImages[currentImageIndex];

        updateNavigation();
    }

    function updateNavigation() {
        const prevButton = document.getElementById('prevButton');
        const nextButton = document.getElementById('nextButton');
        const imageCounter = document.getElementById('imageCounter');
        const currentImageSpan = document.getElementById('currentImage');
        const totalImagesSpan = document.getElementById('totalImages');

        if (allImages.length > 1) {
            prevButton.classList.remove('hidden');
            nextButton.classList.remove('hidden');
            imageCounter.classList.remove('hidden');

            currentImageSpan.textContent = currentImageIndex + 1;
            totalImagesSpan.textContent = allImages.length;
        } else {
            prevButton.classList.add('hidden');
            nextButton.classList.add('hidden');
            imageCounter.classList.add('hidden');
        }
    }

    function handleKeyPress(event) {
        switch(event.key) {
            case 'Escape':
                closeImageModal();
                break;
            case 'ArrowLeft':
                navigateImage(-1);
                break;
            case 'ArrowRight':
                navigateImage(1);
                break;
        }
    }

    // Close modal when clicking outside the image
    document.getElementById('imageModal')?.addEventListener('click', function(event) {
        if (event.target === this) {
            closeImageModal();
        }
    });
</script>