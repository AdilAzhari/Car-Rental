<x-filament-panels::page>
    <!-- Custom styles and modal for vehicle image viewing -->
    <style>
        .vehicle-image-gallery img {
            transition: all 0.3s ease;
        }

        .vehicle-image-gallery img:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .featured-image {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
        }

        /* Modal styles */
        .image-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
        }

        .modal-image {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-close {
            position: absolute;
            top: -40px;
            right: -40px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: #f3f4f6;
            transform: scale(1.1);
        }

        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .modal-nav:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
        }

        .modal-nav.prev {
            left: -60px;
        }

        .modal-nav.next {
            right: -60px;
        }

        .modal-counter {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
        }
    </style>

    <!-- Vehicle Info List -->
    {{ $this->infolist }}

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeImageModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <img id="modalImage" src="" alt="Vehicle Image" class="modal-image">

            <button id="prevBtn" class="modal-nav prev" onclick="navigateImage(-1)" style="display: none;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button id="nextBtn" class="modal-nav next" onclick="navigateImage(1)" style="display: none;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <div id="imageCounter" class="modal-counter" style="display: none;">
                <span id="currentIndex">1</span> / <span id="totalCount">1</span>
            </div>
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        let imagesList = [];

        function initializeImageModal() {
            // Wait for the page to load completely
            setTimeout(() => {
                setupImageClickHandlers();
            }, 1000);
        }

        function setupImageClickHandlers() {
            // Find all images in the infolist and add click handlers
            const images = document.querySelectorAll('[data-testid*="infolist"] img');

            images.forEach((img, index) => {
                if (img.src && img.src.includes('/storage/')) {
                    img.style.cursor = 'pointer';
                    img.addEventListener('click', () => openImageModal(img.src));

                    // Add hover effect
                    img.addEventListener('mouseenter', () => {
                        img.style.transform = 'scale(1.05)';
                        img.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.2)';
                    });

                    img.addEventListener('mouseleave', () => {
                        img.style.transform = 'scale(1)';
                        img.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
                    });
                }
            });
        }

        function openImageModal(imageSrc) {
            // Collect all vehicle images
            imagesList = [];
            const images = document.querySelectorAll('[data-testid*="infolist"] img');

            images.forEach(img => {
                if (img.src && img.src.includes('/storage/')) {
                    imagesList.push(img.src);
                }
            });

            // Find current image index
            currentImageIndex = imagesList.findIndex(src => src === imageSrc);
            if (currentImageIndex === -1) currentImageIndex = 0;

            // Show modal
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';

            // Update navigation
            updateNavigation();

            // Add keyboard listeners
            document.addEventListener('keydown', handleKeyPress);

            // Disable body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.removeEventListener('keydown', handleKeyPress);
            document.body.style.overflow = 'auto';
        }

        function navigateImage(direction) {
            if (imagesList.length <= 1) return;

            currentImageIndex += direction;

            if (currentImageIndex >= imagesList.length) {
                currentImageIndex = 0;
            } else if (currentImageIndex < 0) {
                currentImageIndex = imagesList.length - 1;
            }

            document.getElementById('modalImage').src = imagesList[currentImageIndex];
            updateNavigation();
        }

        function updateNavigation() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const counter = document.getElementById('imageCounter');

            if (imagesList.length > 1) {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
                counter.style.display = 'block';

                document.getElementById('currentIndex').textContent = currentImageIndex + 1;
                document.getElementById('totalCount').textContent = imagesList.length;
            } else {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                counter.style.display = 'none';
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
        document.getElementById('imageModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeImageModal();
            }
        });

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', initializeImageModal);

        // Re-initialize after Livewire updates
        document.addEventListener('livewire:navigated', initializeImageModal);

        // For older Livewire versions
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('message.processed', initializeImageModal);
        }
    </script>
</x-filament-panels::page>