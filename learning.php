<?php
session_start();
require_once 'includes/header.php';
?>

<style>
    .video-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .video-card {
        background-color: #1f2937;
        border: 1px solid #374151;
        border-radius: 0.75rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .video-card:hover {
        transform: translateY(-4px);
        border-color: #3b82f6;
        box-shadow: 0 10px 30px -10px rgba(59, 130, 246, 0.3);
    }
    
    .video-thumbnail {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        background-color: #2d3748;
        overflow: hidden;
    }
    
    .video-thumbnail img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .video-card:hover .video-thumbnail img {
        transform: scale(1.05);
    }
    
    .video-duration {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .video-info {
        padding: 1.25rem;
    }
    
    .video-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .video-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.9rem;
        color: #9ca3af;
    }
    
    .video-level {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background-color: #374151;
        border-radius: 9999px;
        font-size: 0.8rem;
        color: #e5e7eb;
    }
    
    .category-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .category-tab {
        padding: 0.5rem 1.25rem;
        background-color: #1f2937;
        border: 1px solid #374151;
        border-radius: 9999px;
        color: #9ca3af;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .category-tab:hover {
        background-color: #374151;
        color: white;
    }
    
    .category-tab.active {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .search-bar {
        background-color: #1f2937;
        border: 1px solid #374151;
        border-radius: 9999px;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        color: white;
        width: 100%;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%239ca3af' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 0.75rem center;
        background-size: 1.25rem;
    }
    
    .search-bar:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .featured-section {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 1.5rem;
        padding: 3rem;
        margin-bottom: 3rem;
    }
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Learn Dance Online</h1>
        <p class="text-xl text-gray-400 max-w-3xl mx-auto">
            Master various dance styles with our comprehensive video tutorials. 
            From beginner to advanced, learn at your own pace.
        </p>
    </div>

    <!-- Featured Video Section -->
    <div class="featured-section mb-12">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <span class="text-blue-500 font-semibold text-sm uppercase tracking-wider">Featured Tutorial</span>
                <h2 class="text-3xl font-bold text-white mt-2 mb-4">Introduction to Contemporary Dance</h2>
                <p class="text-gray-300 mb-6">
                    Learn the fundamentals of contemporary dance with professional instructor Sarah Johnson. 
                    This comprehensive tutorial covers basic movements, techniques, and a simple routine.
                </p>
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-gray-300">4.9 (120 reviews)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-300">45 minutes</span>
                    </div>
                </div>
                <a href="#" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Watch Now
                </a>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                     alt="Contemporary Dance" 
                     class="rounded-xl shadow-2xl">
                <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/20 to-transparent rounded-xl"></div>
            </div>
        </div>
    </div>

    <!-- Video Grid -->
    <div class="video-grid">
        <!-- Video Card 1 -->
        <div class="video-card">
            <div class="video-thumbnail">
                <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Ballet Basics">
                <span class="video-duration">25 min</span>
            </div>
            <div class="video-info">
                <h3 class="video-title">Ballet Basics: Essential Positions & Movements</h3>
                <div class="video-meta mb-2">
                    <span>Beginner</span>
                    <span>•</span>
                    <span>Emma Watson</span>
                </div>
                <p class="text-gray-400 text-sm mb-3">Learn the fundamental positions and basic movements of classical ballet.</p>
                <div class="flex items-center justify-between">
                    <span class="video-level">Beginner</span>
                    <button class="text-blue-500 hover:text-blue-400 text-sm font-medium">Watch →</button>
                </div>
            </div>
        </div>

        <!-- Video Card 2 -->
        <div class="video-card">
            <div class="video-thumbnail">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSZBu8CcwOqOH_naMwtQKbfZKb7q2At-mzviw&s" alt="Hip Hop">
                <span class="video-duration">35 min</span>
            </div>
            <div class="video-info">
                <h3 class="video-title">Hip Hop Fundamentals: Grooves & Moves</h3>
                <div class="video-meta mb-2">
                    <span>Intermediate</span>
                    <span>•</span>
                    <span>Mike Chen</span>
                </div>
                <p class="text-gray-400 text-sm mb-3">Master the essential hip hop grooves and popular dance moves.</p>
                <div class="flex items-center justify-between">
                    <span class="video-level">Intermediate</span>
                    <button class="text-blue-500 hover:text-blue-400 text-sm font-medium">Watch →</button>
                </div>
            </div>
        </div>

        <!-- Video Card 3 -->
        <div class="video-card">
            <div class="video-thumbnail">
                <img src="https://res.cloudinary.com/hz3gmuqw6/image/upload/c_fill,f_auto,q_60,w_750/v1/goldenapron/62321f5b3f382" alt="Salsa">
                <span class="video-duration">40 min</span>
            </div>
            <div class="video-info">
                <h3 class="video-title">Salsa Dancing: Basic Steps & Turns</h3>
                <div class="video-meta mb-2">
                    <span>Beginner</span>
                    <span>•</span>
                    <span>Carlos Rivera</span>
                </div>
                <p class="text-gray-400 text-sm mb-3">Learn the basic salsa steps, turns, and partner work fundamentals.</p>
                <div class="flex items-center justify-between">
                    <span class="video-level">Beginner</span>
                    <button class="text-blue-500 hover:text-blue-400 text-sm font-medium">Watch →</button>
                </div>
            </div>
        </div>

        <!-- Video Card 4 -->
        <div class="video-card">
            <div class="video-thumbnail">
                <img src="https://media.istockphoto.com/id/490581749/photo/contemporary-dance.jpg?s=612x612&w=0&k=20&c=wiBmZ_zqEKWH-4eI21G06fzhAa6H3zFaxUfpKK0Dn-g=" alt="Contemporary">
                <span class="video-duration">45 min</span>
            </div>
            <div class="video-info">
                <h3 class="video-title">Contemporary Dance: Expression & Flow</h3>
                <div class="video-meta mb-2">
                    <span>Advanced</span>
                    <span>•</span>
                    <span>Sarah Johnson</span>
                </div>
                <p class="text-gray-400 text-sm mb-3">Explore emotional expression and fluid movements in contemporary dance.</p>
                <div class="flex items-center justify-between">
                    <span class="video-level">Advanced</span>
                    <button class="text-blue-500 hover:text-blue-400 text-sm font-medium">Watch →</button>
                </div>
            </div>
        </div>

        <!-- Video Card 5 -->
        <div class="video-card">
            <div class="video-thumbnail">
                <img src="https://images.unsplash.com/photo-1504609813442-a8924e83f76e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Jazz">
                <span class="video-duration">30 min</span>
            </div>
            <div class="video-info">
                <h3 class="video-title">Jazz Dance: Technique & Style</h3>
                <div class="video-meta mb-2">
                    <span>Intermediate</span>
                    <span>•</span>
                    <span>Lisa Brown</span>
                </div>
                <p class="text-gray-400 text-sm mb-3">Learn jazz technique, isolations, and classic jazz style.</p>
                <div class="flex items-center justify-between">
                    <span class="video-level">Intermediate</span>
                    <button class="text-blue-500 hover:text-blue-400 text-sm font-medium">Watch →</button>
                </div>
            </div>
        </div>

        <!-- Video Card 6 -->
        <div class="video-card">
            <div class="video-thumbnail">
                <img src="https://www.nyc-arts.org/wp-content/uploads/2016/04/D27A3536.jpg-1.1-MB-RIM-2014-PHOTO-AMANDA-GENTILE.jpg" alt="Tap">
                <span class="video-duration">35 min</span>
            </div>
            <div class="video-info">
                <h3 class="video-title">Tap Dance: Rhythms & Combinations</h3>
                <div class="video-meta mb-2">
                    <span>Beginner</span>
                    <span>•</span>
                    <span>Tom Wilson</span>
                </div>
                <p class="text-gray-400 text-sm mb-3">Master basic tap rhythms, steps, and fun combinations.</p>
                <div class="flex items-center justify-between">
                    <span class="video-level">Beginner</span>
                    <button class="text-blue-500 hover:text-blue-400 text-sm font-medium">Watch →</button>
                </div>
            </div>
        </div>
    </div>

<script>
// Category tab functionality
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        // Here you would filter videos based on category
    });
});

// Search functionality
document.querySelector('.search-bar').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    // Here you would implement search filtering
});
</script>

<?php require_once 'includes/footer.php'; ?>