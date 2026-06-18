<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\about_us\\edit.blade.php';
$content = file_get_contents($file);

$tabsSearch = <<<EOT
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="impacts-tab" data-bs-toggle="tab" data-bs-target="#impacts" type="button" role="tab">
                            <i class="fas fa-chart-line me-2"></i> Impact Stats
                        </button>
                    </li>
                </ul>
EOT;

$tabsReplace = <<<EOT
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="impacts-tab" data-bs-toggle="tab" data-bs-target="#impacts" type="button" role="tab">
                            <i class="fas fa-chart-line me-2"></i> Impact Stats
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="corevalues-tab" data-bs-toggle="tab" data-bs-target="#corevalues" type="button" role="tab">
                            <i class="fas fa-heart me-2"></i> Core Values
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="founders-tab" data-bs-toggle="tab" data-bs-target="#founders" type="button" role="tab">
                            <i class="fas fa-users me-2"></i> Founders
                        </button>
                    </li>
                </ul>
EOT;

$panesSearch = <<<EOT
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Offer Modal -->
EOT;

$panesReplace = <<<EOT
                    <!-- Core Values Tab -->
                    <div class="tab-pane fade" id="corevalues" role="tabpanel">
                        <form action="{{ route('admin.about_us.update') }}" method="POST">
                            @csrf
                            <h5 class="fw-bold mb-4">Core Values (Mission, Vision, Philosophy)</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Mission</label>
                                <textarea name="mission_text" class="form-control" rows="4">{{ \$page->mission_text }}</textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Vision</label>
                                <textarea name="vision_text" class="form-control" rows="4">{{ \$page->vision_text }}</textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Philosophy</label>
                                <textarea name="philosophy_text" class="form-control" rows="4">{{ \$page->philosophy_text }}</textarea>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Save Core Values</button>
                            </div>
                        </form>
                    </div>

                    <!-- Founders Tab -->
                    <div class="tab-pane fade" id="founders" role="tabpanel">
                        <form action="{{ route('admin.about_us.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h5 class="fw-bold mb-4">Meet Our Founders</h5>
                            
                            <div class="row">
                                <!-- Founder 1 -->
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold text-primary mb-3">Founder 1 (Left)</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="founder_1_name" class="form-control" value="{{ \$page->founder_1_name }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="founder_1_image" class="form-control">
                                        @if(\$page->founder_1_image)
                                            <img src="{{ asset(\$page->founder_1_image) }}" width="100" class="mt-2 rounded">
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Facebook Link</label>
                                        <input type="text" name="founder_1_facebook" class="form-control" value="{{ \$page->founder_1_facebook }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">LinkedIn Link</label>
                                        <input type="text" name="founder_1_linkedin" class="form-control" value="{{ \$page->founder_1_linkedin }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Twitter Link</label>
                                        <input type="text" name="founder_1_twitter" class="form-control" value="{{ \$page->founder_1_twitter }}">
                                    </div>
                                </div>
                                
                                <!-- Founder 2 -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-primary mb-3">Founder 2 (Right)</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="founder_2_name" class="form-control" value="{{ \$page->founder_2_name }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="founder_2_image" class="form-control">
                                        @if(\$page->founder_2_image)
                                            <img src="{{ asset(\$page->founder_2_image) }}" width="100" class="mt-2 rounded">
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Facebook Link</label>
                                        <input type="text" name="founder_2_facebook" class="form-control" value="{{ \$page->founder_2_facebook }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">LinkedIn Link</label>
                                        <input type="text" name="founder_2_linkedin" class="form-control" value="{{ \$page->founder_2_linkedin }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Twitter Link</label>
                                        <input type="text" name="founder_2_twitter" class="form-control" value="{{ \$page->founder_2_twitter }}">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Common Message (below founders)</label>
                                <textarea name="founders_common_message" class="form-control" rows="3">{{ \$page->founders_common_message }}</textarea>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Save Founders Content</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Offer Modal -->
EOT;

$content = str_replace($tabsSearch, $tabsReplace, $content);
$content = str_replace($panesSearch, $panesReplace, $content);

file_put_contents($file, $content);
echo "Patched admin tabs successfully.";
