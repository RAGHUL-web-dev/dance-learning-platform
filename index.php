<?php
session_start();
require_once 'includes/header.php';
?>

<!-- Hero Section with Dramatic Black & White UI, Color Images -->
<div class="relative bg-black overflow-hidden">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Content - Black & White UI -->
            <div class="space-y-8">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-sm">
                    <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                    Live Classes 24/7
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Dance Like No One's
                    <span class="text-white block mt-2">Watching, But Everyone Is</span>
                </h1>
                <p class="text-xl text-gray-300 max-w-3xl">
                    Join live, interactive dance classes with professional instructors. 
                    Get real-time feedback through our HD video platform. Watch, learn, and groove together.
                </p>
                
                <!-- CTA Buttons - Black & White -->
                <div class="flex flex-wrap gap-4">
                    <a href="<?php echo BASE_PATH; ?>register.php" class="bg-white text-black px-8 py-4 rounded-lg font-medium hover:bg-gray-200 transition transform hover:scale-105 shadow-lg">
                        Start Free Trial
                    </a>
                    <a href="#how-it-works" class="bg-white/10 text-white px-8 py-4 rounded-lg font-medium hover:bg-white/20 transition flex items-center border border-white/20">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                        </svg>
                        Watch Demo
                    </a>
                </div>

                <!-- Live Class Preview - Black & White UI with Color Avatars -->
                <div class="flex items-center space-x-6 pt-6">
                    <div class="flex -space-x-2">
                        <img class="w-10 h-10 rounded-full border-2 border-white/30" src="https://images.unsplash.com/photo-1494790108777-296ef5a5d5e8?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" alt="Student">
                        <img class="w-10 h-10 rounded-full border-2 border-white/30" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" alt="Student">
                        <img class="w-10 h-10 rounded-full border-2 border-white/30" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" alt="Student">
                        <img class="w-10 h-10 rounded-full border-2 border-white/30" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" alt="Student">
                    </div>
                    <div class="text-gray-300">
                        <span class="font-bold text-white">500+</span> students learning now
                    </div>
                </div>
            </div>

            <!-- Right Image - Color Video Conference Mockup -->
            <div class="relative hidden lg:block">
                <div class="relative bg-black rounded-2xl shadow-2xl overflow-hidden border border-white/10">
                    <div class="absolute top-0 left-0 right-0 bg-black/90 px-4 py-2 flex items-center border-b border-white/10">
                        <div class="flex space-x-2">
                            <div class="w-3 h-3 bg-white/30 rounded-full"></div>
                            <div class="w-3 h-3 bg-white/30 rounded-full"></div>
                            <div class="w-3 h-3 bg-white/30 rounded-full"></div>
                        </div>
                        <div class="flex-1 text-center text-sm text-gray-400">Live Dance Class • 4 participants</div>
                    </div>
                    <div class="grid grid-cols-2 gap-1 p-1 pt-12">
                        <img src="https://dance-teacher.com/wp-content/uploads/2022/01/PNBSum19A-0205-1024x683.jpg" class="w-full h-40 object-cover rounded" alt="Dance instructor">
                        <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="w-full h-40 object-cover rounded" alt="Student dancing">
                        <img src="https://images.unsplash.com/photo-1504609813442-a8924e83f76e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="w-full h-40 object-cover rounded" alt="Student dancing">
                        <img src="https://images.jdmagicbox.com/v2/comp/chennai/r9/044pxx44.xx44.151022185616.d8r9/catalogue/high-on-dance-teynampet-chennai-dance-classes-zy57nt8n0v.jpg" class="w-full h-40 object-cover rounded" alt="Dance class">
                    </div>
                    <div class="absolute bottom-4 left-4 bg-white text-black px-3 py-1 rounded-full text-sm flex items-center font-medium">
                        <span class="w-2 h-2 bg-black rounded-full mr-2 animate-pulse"></span>
                        LIVE NOW: Hip Hop Basics
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Bar - Black & White -->
<div class="bg-black border-y border-white/10">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-3xl font-bold text-white">150+</div>
                <div class="text-sm text-gray-400 mt-1">Daily Live Classes</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">75+</div>
                <div class="text-sm text-gray-400 mt-1">Expert Instructors</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">15k+</div>
                <div class="text-sm text-gray-400 mt-1">Active Students</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">4.9</div>
                <div class="text-sm text-gray-400 mt-1">Rating (5.8k reviews)</div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section - Black & White UI with Color Images -->
<div id="how-it-works" class="max-w-7xl mx-auto py-20 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
        <span class="text-white/60 font-semibold tracking-wide uppercase">Simple Process</span>
        <h2 class="text-3xl md:text-4xl font-bold text-white mt-2">How Live Dance Learning Works</h2>
        <p class="mt-4 text-gray-400 max-w-2xl mx-auto">Three simple steps to start your dance journey</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
            <div class="relative inline-block">
                <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto border border-white/20">
                    <span class="text-3xl text-white">1</span>
                </div>
                <div class="absolute -right-4 top-1/2 hidden md:block text-white/20">→</div>
            </div>
            <h3 class="text-xl font-bold text-white mt-6">Create Account</h3>
            <p class="text-gray-400 mt-3">Sign up as a student or instructor in under 2 minutes</p>
            <img src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80" class="w-32 h-32 object-cover rounded-full mx-auto mt-4 border-2 border-white/20">
        </div>
        <div class="text-center">
            <div class="relative inline-block">
                <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto border border-white/20">
                    <span class="text-3xl text-white">2</span>
                </div>
                <div class="absolute -right-4 top-1/2 hidden md:block text-white/20">→</div>
            </div>
            <h3 class="text-xl font-bold text-white mt-6">Book a Class</h3>
            <p class="text-gray-400 mt-3">Browse schedules and book your preferred dance style</p>
            <img src="https://images.unsplash.com/photo-1504609813442-a8924e83f76e?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80" class="w-32 h-32 object-cover rounded-full mx-auto mt-4 border-2 border-white/20">
        </div>
        <div class="text-center">
            <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto border border-white/20">
                <span class="text-3xl text-white">3</span>
            </div>
            <h3 class="text-xl font-bold text-white mt-6">Join Live Session</h3>
            <p class="text-gray-400 mt-3">Click the meeting link and start dancing in real-time</p>
            <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80" class="w-32 h-32 object-cover rounded-full mx-auto mt-4 border-2 border-white/20">
        </div>
    </div>
</div>

<!-- Zig Zag Section 1: Text Left (B&W UI), Image Right (Color) -->
<div class="bg-black/90 border-y border-white/10 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="inline-block px-3 py-1 bg-white/10 border border-white/20 rounded-full text-white/80 text-sm">Real-Time Interaction</div>
                <h2 class="text-3xl font-bold text-white">See Your Instructor. Get Instant Feedback.</h2>
                <p class="text-gray-300 text-lg">Unlike pre-recorded videos, our live platform lets instructors watch your movements and correct your form immediately. It's just like a real dance studio, but from home.</p>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">Two-way video communication with your instructor</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">Verbal and visual corrections in real-time</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">Ask questions and get answers immediately</span>
                    </li>
                </ul>
                <a href="#" class="inline-flex items-center text-white hover:text-gray-300 font-medium">
                    See how it works
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1578632767115-351597cf2477?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                     alt="Dance instructor teaching online" 
                     class="rounded-2xl shadow-2xl border border-white/10">
                <div class="absolute -bottom-6 -left-6 bg-black p-4 rounded-xl border border-white/10 shadow-xl max-w-xs">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white">👩‍🏫</div>
                        <div class="ml-3">
                            <p class="text-white text-sm font-medium">"Great job on that turn!"</p>
                            <p class="text-gray-400 text-xs">- Instructor Maria</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Zig Zag Section 2: Image Left (Color), Text Right (B&W UI) -->
<div class="bg-black py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="relative order-2 lg:order-1">
                <img src="https://images.unsplash.com/photo-1518834107812-67b0b7c58434?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                     alt="Multiple students in a live class" 
                     class="rounded-2xl shadow-2xl border border-white/10">
                <div class="absolute -top-6 -right-6 bg-black p-3 rounded-xl border border-white/10">
                    <div class="flex space-x-2">
                        <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center text-white">👤</div>
                        <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center text-white">👤</div>
                        <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center text-white">👤</div>
                    </div>
                </div>
                <!-- Additional color dance image overlay -->
                <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80" 
                     class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full border-4 border-black object-cover">
            </div>
            <div class="space-y-6 order-1 lg:order-2">
                <div class="inline-block px-3 py-1 bg-white/10 border border-white/20 rounded-full text-white/80 text-sm">Group Learning</div>
                <h2 class="text-3xl font-bold text-white">Learn Together, Grow Together</h2>
                <p class="text-gray-300 text-lg">Join group classes and dance with students from around the world. See their video feeds, learn from their questions, and feel the energy of a real dance community.</p>
                <div class="grid grid-cols-2 gap-4 mt-8">
                    <div class="bg-black border border-white/10 p-4 rounded-xl">
                        <div class="text-2xl font-bold text-white">8-12</div>
                        <div class="text-gray-400 text-sm">Students per class</div>
                    </div>
                    <div class="bg-black border border-white/10 p-4 rounded-xl">
                        <div class="text-2xl font-bold text-white">15+</div>
                        <div class="text-gray-400 text-sm">Styles available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dance Styles Gallery - Black & White UI with Color Images -->
<div class="bg-black/90 py-20 border-y border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-white">Dance Styles Gallery</h2>
            <p class="mt-4 text-gray-400">Explore our diverse range of dance classes</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Hip Hop</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://images.stockcake.com/public/1/e/d/1ede2eab-d29e-407f-ad6e-05e979c203ec_large/ballet-rose-shower-stockcake.jpg " 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Ballet</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://images.unsplash.com/photo-1504609813442-a8924e83f76e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Contemporary</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFhFZeM7QCLD4SByIsd12fuejlF_ZqSlM_3Q&s" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Salsa</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://voca-land.sgp1.cdn.digitaloceanspaces.com/0/1757692302837/2dbae7d9.jpg" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Breakdance</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://images.unsplash.com/photo-1518834107812-67b0b7c58434?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Jazz</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Latin</span>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl aspect-square border border-white/10">
                <img src="https://images.unsplash.com/photo-1535525153412-5a42439a210d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" 
                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent flex items-end p-4">
                    <span class="text-white text-xl font-bold">Tap</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Zig Zag Section 3: Text Left (B&W UI), Image Right (Color) -->
<div class="bg-black py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="inline-block px-3 py-1 bg-white/10 border border-white/20 rounded-full text-white/80 text-sm">For Instructors</div>
                <h2 class="text-3xl font-bold text-white">Turn Your Passion into Profession</h2>
                <p class="text-gray-300 text-lg">Professional dancers and instructors can create their own virtual studio. Schedule classes, manage students, and earn from anywhere in the world.</p>
                <div class="bg-black border border-white/10 p-6 rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-white font-medium">Your earnings potential</span>
                        <span class="text-white/60">↑ 45% this month</span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Private lessons</span>
                            <span class="text-white">$45-75/hr</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Group classes</span>
                            <span class="text-white">$120-200/class</span>
                        </div>
                    </div>
                </div>
                <a href="<?php echo BASE_PATH; ?>become-instructor.php" class="inline-flex items-center text-white hover:text-gray-300 font-medium">
                    Become an instructor
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
            <div class="relative">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS49qdo0wWcTFPI0MzNVbSaimOEpYOjUTZfAw&s" 
                     alt="Dance instructor teaching" 
                     class="rounded-2xl shadow-2xl border border-white/10">
                <!-- Floating color dance image -->
                <img src="https://dance-teacher.com/wp-content/uploads/2022/01/PNBSum19A-0205-1024x683.jpg" 
                     class="absolute -top-6 -right-6 w-32 h-32 rounded-full border-4 border-black object-cover">
            </div>
        </div>
    </div>
</div>

<!-- Testimonials with Color Images - B&W UI -->
<div class="bg-black/90 py-20 border-y border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-white">What Our Dancers Say</h2>
            <p class="mt-4 text-gray-400">Join thousands of happy students</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-black border border-white/10 p-6 rounded-xl">
                <div class="flex items-center mb-4">
                    <img src="https://images.unsplash.com/photo-1494790108777-296ef5a5d5e8?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" class="w-12 h-12 rounded-full border-2 border-white/20">
                    <div class="ml-4">
                        <h4 class="text-white font-medium">Sarah Johnson</h4>
                        <p class="text-gray-400 text-sm">Ballet Student</p>
                    </div>
                </div>
                <p class="text-gray-300">"The live feedback is incredible! My instructor corrects my posture in real-time, just like in-person classes."</p>
                <div class="flex mt-3 text-white/60">★★★★★</div>
            </div>
            <div class="bg-black border border-white/10 p-6 rounded-xl">
                <div class="flex items-center mb-4">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" class="w-12 h-12 rounded-full border-2 border-white/20">
                    <div class="ml-4">
                        <h4 class="text-white font-medium">Mike Chen</h4>
                        <p class="text-gray-400 text-sm">Hip Hop Student</p>
                    </div>
                </div>
                <p class="text-gray-300">"Been dancing for 5 years and this platform takes online learning to another level. The video quality is amazing."</p>
                <div class="flex mt-3 text-white/60">★★★★★</div>
            </div>
            <div class="bg-black border border-white/10 p-6 rounded-xl">
                <div class="flex items-center mb-4">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" class="w-12 h-12 rounded-full border-2 border-white/20">
                    <div class="ml-4">
                        <h4 class="text-white font-medium">Elena Rodriguez</h4>
                        <p class="text-gray-400 text-sm">Instructor</p>
                    </div>
                </div>
                <p class="text-gray-300">"As an instructor, I can reach students worldwide. The platform makes teaching online feel natural and effective."</p>
                <div class="flex mt-3 text-white/60">★★★★★</div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA - Black & White UI -->
<div class="max-w-7xl mx-auto py-20 px-4 sm:px-6 lg:px-8">
    <div class="bg-black border border-white/10 rounded-3xl p-12 md:p-16">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Dance?</h2>
            <p class="text-xl text-gray-300 mb-8">Join the #1 live interactive dance platform today. First class is on us!</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo BASE_PATH; ?>register.php" class="bg-white text-black px-8 py-4 rounded-xl font-medium hover:bg-gray-200 transition transform hover:scale-105 shadow-lg">
                    Start Your Free Trial
                </a>
                <a href="<?php echo BASE_PATH; ?>classes.php" class="bg-white/10 text-white px-8 py-4 rounded-xl font-medium hover:bg-white/20 transition border border-white/20">
                    Browse Classes
                </a>
            </div>
            <p class="text-gray-500 text-sm mt-6">No credit card required • Cancel anytime</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>