@extends('layouts.app')

@section('content')
    <!-- Main Content -->
    <div class="app-content-header">
        <div class="container-fluid">
            <!-- Current Date and Time -->
            <div class="d-flex justify-content-start align-items-center">
                <p class="text-muted mb-3">{{ now()->addHours(7)->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>
            </div>

            <div class="row">
                <!-- Study Progress Chart (Left) -->
                <div class="col-md-6">
                    <div class="chart-card p-3">
                        <h5 class="card-title">Kemajuan Studi</h5>
                        <canvas id="kemajuanStudiChart" height="400"></canvas>
                    </div>
                </div>

                <!-- Attendance Frequency Chart (Right) -->
                <div class="col-md-6">
                    <div class="chart-card p-3">
                        <h5 class="border-bottom-line text-start">FREKUENSI KEHADIRAN PERWALIAN MAHASISWA</h5>
                        @if (!empty($attendanceData['dates']))
                            <canvas id="frekuensiKehadiranChart" height="400"></canvas>
                        @else
                            <p class="text-muted text-start">Belum ada data kehadiran.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Calendar Download Buttons -->
            <div class="row gap-4 mt-4">
                <div class="col-md-12 text-center">
                    @if ($akademik)
                        <a href="{{ asset('storage/' . $akademik->file_path) }}" target="_blank" class="btn btn-primary me-4">
                            Unduh Kalender Akademik!
                        </a>
                    @else
                        <button class="btn btn-primary me-2" disabled>Kalender Akademik Belum Tersedia</button>
                    @endif

                    @if ($bem)
                        <a href="{{ asset('storage/' . $bem->file_path) }}" target="_blank" class="btn btn-primary">
                            Unduh Kalender NonAkademik!
                        </a>
                    @else
                        <button class="btn btn-primary" disabled>Kalender BEM Belum Tersedia</button>
                    @endif
                </div>
            </div>

            <!-- Advertisement Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="ad-section">
                        <h5 class="ad-section-title">Simba Chat</h5>
                        @if ($adError || empty($advertisements))
                            <div class="no-posts-container">
                                <p class="no-posts">Couldn’t load posts</p>
                                <button onclick="refreshAds()" class="refresh-button rounded-full" aria-label="Refresh posts">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8" />
                                        <path d="M21 3v5h-5" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <div class="ad-posts" id="ad-posts">
                                @foreach (array_slice($advertisements, 0, 10) as $ad)
                                    <a href="http://localhost:3000" target="_blank" class="ad-card">
                                        @if ($ad['image_data'])
                                            <img src="data:{{ $ad['image_mime_type'] }};base64,{{ $ad['image_data'] }}" alt="{{ $ad['title'] }}" class="ad-image">
                                        @else
                                            <p class="text-gray-400">Image unavailable</p>
                                        @endif
                                        <div class="ad-overlay">
                                            <h3>{{ $ad['title'] }}</h3>
                                            <p>{{ Str::limit($ad['description'], 30) }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>

    @if (!empty($attendanceData['dates']))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    const ctx = document.getElementById('frekuensiKehadiranChart').getContext('2d');
                    if (!ctx) {
                        throw new Error('Canvas element "frekuensiKehadiranChart" not found');
                    }

                    const attendanceData = @json($attendanceData);
                    console.log('Raw Attendance Data:', attendanceData);

                    if (!attendanceData || !attendanceData.dates || !attendanceData.values || !attendanceData.colors) {
                        throw new Error('Invalid attendance data: missing required fields');
                    }

                    const dates = attendanceData.dates;
                    const values = attendanceData.values;
                    const colors = attendanceData.colors;

                    console.log('Parsed Values:', values);
                    console.log('Colors:', colors);
                    console.log('Dates:', dates);

                    const dataPoints = dates.map((date, index) => ({
                        x: date,
                        y: values[index]
                    }));

                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Attendance',
                                data: dataPoints,
                                borderColor: '#007bff',
                                backgroundColor: colors,
                                pointBackgroundColor: colors,
                                pointBorderColor: colors,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                fill: false,
                                tension: 0,
                                segment: {
                                    borderColor: ctx => {
                                        const prevIndex = ctx.p0DataIndex;
                                        const currIndex = ctx.p1DataIndex;
                                        const prevValue = values[prevIndex];
                                        const currValue = values[currIndex];

                                        console.log('Segment:', {
                                            prevIndex: prevIndex,
                                            currIndex: currIndex,
                                            prevValue: prevValue,
                                            currValue: currValue,
                                            prevDate: dates[prevIndex],
                                            currDate: dates[currIndex]
                                        });

                                        if (prevValue === 0.5 && currValue === 0.5) {
                                            console.log('Hadir to Hadir -> Blue');
                                            return '#007bff';
                                        }
                                        if (prevValue === -0.5 && currValue === -0.5) {
                                            console.log('Tidak Hadir to Tidak Hadir -> Red');
                                            return '#dc3545';
                                        }
                                        if (prevValue === 0.5 && currValue === -0.5) {
                                            console.log('Hadir to Tidak Hadir -> Red');
                                            return '#dc3545';
                                        }
                                        if (prevValue === -0.5 && currValue === 0.5) {
                                            console.log('Tidak Hadir to Hadir -> Blue');
                                            return '#007bff';
                                        }
                                        console.log('Fallback -> Blue');
                                        return '#007bff';
                                    }
                                }
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    min: -1,
                                    max: 1,
                                    ticks: {
                                        stepSize: 1,
                                        callback: function (value) {
                                            if (value === 0.5) return 'Hadir';
                                            if (value === -0.5) return 'Tidak Hadir';
                                            return '';
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        generateLabels: function (chart) {
                                            return [
                                                {
                                                    text: 'Hadir',
                                                    fillStyle: '#007bff',
                                                    strokeStyle: '#007bff',
                                                    pointStyle: 'line',
                                                    lineWidth: 2
                                                },
                                                {
                                                    text: 'Tidak Hadir',
                                                    fillStyle: '#dc3545',
                                                    strokeStyle: '#dc3545',
                                                    pointStyle: 'line',
                                                    lineWidth: 2
                                                }
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error rendering attendance chart:', error);
                }
            });
        </script>
    @endif

    <script>
        function refreshAds() {
            console.log('refreshAds: Attempting to fetch advertisements');
            fetch('/api/advertisements', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to fetch advertisements: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('refreshAds: Fetched advertisements:', data);
                    const section = document.querySelector('.ad-section');
                    const noPostsContainer = section.querySelector('.no-posts-container');
                    const adPosts = section.querySelector('#ad-posts');

                    if (data.length === 0) {
                        console.log('refreshAds: No posts available');
                        if (noPostsContainer) {
                            noPostsContainer.style.display = 'flex';
                        }
                        if (adPosts) {
                            adPosts.style.display = 'none';
                        }
                        return;
                    }

                    // Limit to first 10 posts
                    const limitedData = data.slice(0, 10);
                    localStorage.setItem('advertisements', JSON.stringify(limitedData));

                    // Update posts and duplicate for scrolling
                    if (adPosts) {
                        const postHTML = limitedData.map(ad => `
                            <a href="http://localhost:3000" target="_blank" class="ad-card">
                                ${ad.image_data ? `<img src="data:${ad.image_mime_type};base64,${ad.image_data}" alt="${ad.title}" class="ad-image">` : '<p class="text-gray-400">Image unavailable</p>'}
                                <div class="ad-overlay">
                                    <h3>${ad.title}</h3>
                                    <p>${ad.description.length > 30 ? ad.description.substring(0, 30) + '...' : ad.description}</p>
                                </div>
                            </a>
                        `).join('');
                        adPosts.innerHTML = postHTML + postHTML + postHTML; // Triple for smoother loop
                        adPosts.style.display = 'flex';
                    }
                    if (noPostsContainer) {
                        noPostsContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('refreshAds: Error fetching advertisements:', error);
                    const section = document.querySelector('.ad-section');
                    const noPostsContainer = section.querySelector('.no-posts-container');
                    const adPosts = section.querySelector('#ad-posts');
                    if (noPostsContainer) {
                        noPostsContainer.style.display = 'flex';
                    }
                    if (adPosts) {
                        adPosts.style.display = 'none';
                    }
                });
        }

        // Seamless looping for ad posts
        document.addEventListener('DOMContentLoaded', function () {
            const adPosts = document.querySelector('#ad-posts');
            if (adPosts && adPosts.children.length > 0) {
                const posts = @json(array_slice($advertisements, 0, 10));
                localStorage.setItem('advertisements', JSON.stringify(posts));
                // Triple posts for smoother looping
                adPosts.innerHTML = adPosts.innerHTML + adPosts.innerHTML + adPosts.innerHTML;
            }

            // Load cached posts if API failed
            if (!adPosts || adPosts.children.length === 0) {
                const cachedPosts = localStorage.getItem('advertisements');
                if (cachedPosts) {
                    const posts = JSON.parse(cachedPosts).slice(0, 10);
                    const section = document.querySelector('.ad-section');
                    const noPostsContainer = section.querySelector('.no-posts-container');
                    const adPosts = section.querySelector('#ad-posts');

                    if (adPosts) {
                        const postHTML = posts.map(ad => `
                            <a href="http://localhost:3000" target="_blank" class="ad-card">
                                ${ad.image_data ? `<img src="data:${ad.image_mime_type};base64,${ad.image_data}" alt="${ad.title}" class="ad-image">` : '<p class="text-gray-400">Image unavailable</p>'}
                                <div class="ad-overlay">
                                    <h3>${ad.title}</h3>
                                    <p>${ad.description.length > 30 ? ad.description.substring(0, 30) + '...' : ad.description}</p>
                                </div>
                            </a>
                        `).join('');
                        adPosts.innerHTML = postHTML + postHTML + postHTML; // Triple posts
                        adPosts.style.display = 'flex';
                    }
                    if (noPostsContainer) {
                        noPostsContainer.style.display = 'none';
                    }
                }
            }

            // Continuous scroll loop
            if (adPosts && adPosts.children.length > 0) {
                let scrollPos = 0;
                const scrollSpeed = 0.5; // Constant speed (pixels per frame)
                const cardWidth = window.innerWidth <= 768 ? 106 : 128; // 100px + 6px mobile, 120px + 8px desktop
                const resetPoint = cardWidth * 10; // After 10 cards
                let animationFrameId = null;

                function scrollAds() {
                    scrollPos += scrollSpeed;
                    adPosts.scrollLeft = scrollPos;

                    // Reset scroll when 10 cards have passed
                    if (scrollPos >= resetPoint) {
                        scrollPos = 0; // Reset to start
                        adPosts.scrollLeft = 0;
                    }

                    animationFrameId = requestAnimationFrame(scrollAds);
                }

                // Start scrolling
                scrollAds();

                // Pause on hover
                adPosts.addEventListener('mouseenter', () => {
                    if (animationFrameId) {
                        cancelAnimationFrame(animationFrameId); // Cancel existing animation
                        animationFrameId = null;
                    }
                    scrollPos = adPosts.scrollLeft; // Save current position
                });

                // Resume on mouse leave
                adPosts.addEventListener('mouseleave', () => {
                    if (!animationFrameId) {
                        scrollAds(); // Restart scrolling
                    }
                });
            }
        });
    </script>
@endsection