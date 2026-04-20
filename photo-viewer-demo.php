<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook-style Photo Viewer Demo - MUBUGA TSS</title>
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/photo-viewer.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .demo-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .demo-header h1 {
            font-size: 2.5rem;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .demo-header p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .demo-section {
            margin-bottom: 40px;
        }

        .demo-section h2 {
            font-size: 1.8rem;
            color: #1a1a1a;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .demo-section p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .image-gallery img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .image-gallery img:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .video-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .video-gallery video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .video-gallery video:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .mixed-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .mixed-gallery img,
        .mixed-gallery video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .mixed-gallery img:hover,
        .mixed-gallery video:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .demo-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .demo-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .demo-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #007bff;
        }

        .feature-card h3 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #666;
            margin: 0;
            line-height: 1.5;
        }

        .keyboard-shortcuts {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .keyboard-shortcuts h3 {
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .shortcut-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .shortcut-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .shortcut-key {
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            border: 1px solid #ddd;
            min-width: 40px;
            text-align: center;
        }

        .shortcut-desc {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .demo-container {
                padding: 20px;
            }

            .demo-header h1 {
                font-size: 2rem;
            }

            .demo-section h2 {
                font-size: 1.5rem;
            }

            .image-gallery,
            .video-gallery,
            .mixed-gallery {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1>Facebook-style Photo Viewer</h1>
            <p>Experience advanced zoom functionality with double-click zoom, scroll wheel zoom, pinch-to-zoom on mobile, and full-screen video support.</p>
        </div>

        <div class="demo-section">
            <h2>Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Double-click Zoom</h3>
                    <p>Double-click any image to zoom in by 2x. Double-click again to reset zoom.</p>
                </div>
                <div class="feature-card">
                    <h3>Scroll Wheel Zoom</h3>
                    <p>Use mouse scroll wheel to zoom in and out smoothly.</p>
                </div>
                <div class="feature-card">
                    <h3>Pinch-to-Zoom</h3>
                    <p>On mobile devices, pinch to zoom in and out with two fingers.</p>
                </div>
                <div class="feature-card">
                    <h3>Drag to Pan</h3>
                    <p>When zoomed in, drag to move around the image.</p>
                </div>
                <div class="feature-card">
                    <h3>Full-screen Video</h3>
                    <p>Videos support full-screen mode with dedicated controls.</p>
                </div>
                <div class="feature-card">
                    <h3>Keyboard Navigation</h3>
                    <p>Use arrow keys, +/- for zoom, and Escape to close.</p>
                </div>
            </div>
        </div>

        <div class="demo-section">
            <h2>Keyboard Shortcuts</h2>
            <div class="keyboard-shortcuts">
                <div class="shortcut-list">
                    <div class="shortcut-item">
                        <span class="shortcut-key">ESC</span>
                        <span class="shortcut-desc">Close viewer</span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-key">Left</span>
                        <span class="shortcut-desc">Previous image</span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-key">Right</span>
                        <span class="shortcut-desc">Next image</span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-key">+</span>
                        <span class="shortcut-desc">Zoom in</span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-key">-</span>
                        <span class="shortcut-desc">Zoom out</span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-key">0</span>
                        <span class="shortcut-desc">Reset zoom</span>
                    </div>
                    <div class="shortcut-item">
                        <span class="shortcut-key">F</span>
                        <span class="shortcut-desc">Fullscreen (video)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="demo-section">
            <h2>Image Gallery</h2>
            <p>Click any image to open it in the photo viewer. Navigate between images using arrow buttons or keyboard.</p>
            <div class="image-gallery" data-photo-gallery>
                <img class="photo-viewer" src="https://picsum.photos/seed/demo1/400/300.jpg" alt="Demo Image 1">
                <img class="photo-viewer" src="https://picsum.photos/seed/demo2/400/300.jpg" alt="Demo Image 2">
                <img class="photo-viewer" src="https://picsum.photos/seed/demo3/400/300.jpg" alt="Demo Image 3">
                <img class="photo-viewer" src="https://picsum.photos/seed/demo4/400/300.jpg" alt="Demo Image 4">
                <img class="photo-viewer" src="https://picsum.photos/seed/demo5/400/300.jpg" alt="Demo Image 5">
                <img class="photo-viewer" src="https://picsum.photos/seed/demo6/400/300.jpg" alt="Demo Image 6">
            </div>
        </div>

        <div class="demo-section">
            <h2>Video Gallery</h2>
            <p>Click any video to open it in the viewer with full-screen support.</p>
            <div class="video-gallery">
                <video class="photo-viewer" poster="https://picsum.photos/seed/video1/400/300.jpg">
                    <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <video class="photo-viewer" poster="https://picsum.photos/seed/video2/400/300.jpg">
                    <source src="https://www.w3schools.com/html/movie.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        <div class="demo-section">
            <h2>Mixed Media Gallery</h2>
            <p>Gallery with both images and videos.</p>
            <div class="mixed-gallery" data-photo-gallery>
                <img class="photo-viewer" src="https://picsum.photos/seed/mixed1/400/300.jpg" alt="Mixed Image 1">
                <video class="photo-viewer" poster="https://picsum.photos/seed/mixed2/400/300.jpg">
                    <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                </video>
                <img class="photo-viewer" src="https://picsum.photos/seed/mixed3/400/300.jpg" alt="Mixed Image 2">
                <img class="photo-viewer" src="https://picsum.photos/seed/mixed4/400/300.jpg" alt="Mixed Image 3">
            </div>
        </div>

        <div class="demo-section">
            <h2>Programmatic Usage</h2>
            <p>Use the JavaScript API to open the photo viewer programmatically:</p>
            <div class="demo-buttons">
                <button class="demo-btn" onclick="openSingleImage()">Open Single Image</button>
                <button class="demo-btn" onclick="openImageGallery()">Open Image Gallery</button>
                <button class="demo-btn" onclick="openVideo()">Open Video</button>
                <button class="demo-btn" onclick="openMixedGallery()">Open Mixed Gallery</button>
            </div>
        </div>
    </div>

    <script src="/MUBUGA-TSS/assets/js/photo-viewer.js"></script>
    <script>
        // Demo functions for programmatic usage
        function openSingleImage() {
            openPhotoViewer('https://picsum.photos/seed/single/1200/800.jpg');
        }

        function openImageGallery() {
            const images = [
                'https://picsum.photos/seed/gallery1/1200/800.jpg',
                'https://picsum.photos/seed/gallery2/1200/800.jpg',
                'https://picsum.photos/seed/gallery3/1200/800.jpg',
                'https://picsum.photos/seed/gallery4/1200/800.jpg',
                'https://picsum.photos/seed/gallery5/1200/800.jpg'
            ];
            openPhotoViewer(images, 2); // Start from index 2
        }

        function openVideo() {
            openPhotoViewer([{
                src: 'https://www.w3schools.com/html/mov_bbb.mp4',
                type: 'video'
            }]);
        }

        function openMixedGallery() {
            const mixed = [
                'https://picsum.photos/seed/mixedapi1/1200/800.jpg',
                {
                    src: 'https://www.w3schools.com/html/mov_bbb.mp4',
                    type: 'video'
                },
                'https://picsum.photos/seed/mixedapi2/1200/800.jpg',
                'https://picsum.photos/seed/mixedapi3/1200/800.jpg'
            ];
            openPhotoViewer(mixed, 1); // Start from video
        }
    </script>
</body>
</html>
