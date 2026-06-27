@extends('admin.layouts.master')

@section('title', 'Manage About Us Page')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold">Manage About Us Page</h3>
        <p class="text-muted">Control all content on the dynamic About Us page.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="aboutUsTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3 px-4 border-0 rounded-0" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-file-alt me-2"></i> General Content
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers" type="button" role="tab">
                            <i class="fas fa-th-large me-2"></i> What We Offer
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab">
                            <i class="fas fa-check-circle me-2"></i> Features
                        </button>
                    </li>
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
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 border-0 rounded-0" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams" type="button" role="tab">
                            <i class="fas fa-user-tie me-2"></i> Team
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4 p-lg-5">
                <div class="tab-content" id="aboutUsTabContent">
                    
                    <!-- General Content Tab -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form action="{{ route('admin.about_us.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h5 class="fw-bold mb-4">Hero Section</h5>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Hero Subtitle</label>
                                    <input type="text" name="hero_subtitle" class="form-control" value="{{ $page->hero_subtitle }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Hero Title</label>
                                    <input type="text" name="hero_title" class="form-control" value="{{ $page->hero_title }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Hero Description</label>
                                    <textarea name="hero_description" class="form-control editor" rows="3">{{ $page->hero_description }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Hero Image</label>
                                    <input type="file" name="hero_image" class="form-control">
                                    <small class="text-muted d-block mt-1">Recommended size: 800x600 px (4:3 aspect ratio)</small>
                                    @if($page->hero_image)
                                        <img src="{{ asset($page->hero_image) }}" width="150" class="mt-2 rounded shadow-sm">
                                    @endif
                                </div>
                            </div>
                            
                            <hr>
                            <h5 class="fw-bold mb-4">Our Story Section</h5>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Story Subtitle</label>
                                    <input type="text" name="story_subtitle" class="form-control" value="{{ $page->story_subtitle }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Story Title</label>
                                    <input type="text" name="story_title" class="form-control" value="{{ $page->story_title }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Story Purpose Text (Overlay Box)</label>
                                    <input type="text" name="story_purpose_text" class="form-control" value="{{ $page->story_purpose_text }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Story Description (HTML allowed)</label>
                                    <textarea name="story_description" class="form-control editor" rows="5">{{ $page->story_description }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Story Image</label>
                                    <input type="file" name="story_image" class="form-control">
                                    <small class="text-muted d-block mt-1">Recommended size: 800x600 px (4:3 aspect ratio)</small>
                                    @if($page->story_image)
                                        <img src="{{ asset($page->story_image) }}" width="150" class="mt-2 rounded shadow-sm">
                                    @endif
                                </div>
                            </div>

                            <hr>
                            <h5 class="fw-bold mb-4">Section Titles</h5>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Offers Subtitle</label>
                                    <input type="text" name="offers_subtitle" class="form-control" value="{{ $page->offers_subtitle }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Offers Title</label>
                                    <input type="text" name="offers_title" class="form-control" value="{{ $page->offers_title }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Features Subtitle</label>
                                    <input type="text" name="features_subtitle" class="form-control" value="{{ $page->features_subtitle }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Features Title</label>
                                    <input type="text" name="features_title" class="form-control" value="{{ $page->features_title }}">
                                </div>
                            </div>

                            <hr>
                            <h5 class="fw-bold mb-4">CTA Section</h5>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">CTA Title</label>
                                    <input type="text" name="cta_title" class="form-control" value="{{ $page->cta_title }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">CTA Description</label>
                                    <textarea name="cta_description" class="form-control" rows="2">{{ $page->cta_description }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Button 1 Text</label>
                                    <input type="text" name="cta_button_1_text" class="form-control" value="{{ $page->cta_button_1_text }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Button 1 Link</label>
                                    <input type="text" name="cta_button_1_link" class="form-control" value="{{ $page->cta_button_1_link }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Button 2 Text</label>
                                    <input type="text" name="cta_button_2_text" class="form-control" value="{{ $page->cta_button_2_text }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Button 2 Link</label>
                                    <input type="text" name="cta_button_2_link" class="form-control" value="{{ $page->cta_button_2_link }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">CTA Image</label>
                                    <input type="file" name="cta_image" class="form-control">
                                    <small class="text-muted d-block mt-1">Recommended size: 500x300 px</small>
                                    @if($page->cta_image)
                                        <img src="{{ asset($page->cta_image) }}" width="150" class="mt-2 rounded shadow-sm">
                                    @endif
                                </div>
                            </div>
                            
                            <hr>
                            <h5 class="fw-bold mb-4">Section Ordering</h5>
                            <p class="text-muted">Reorder the sections below to change their display order on the About Us page.</p>
                            
                            @php
                                $defaultSections = ['hero' => 'Hero Section', 'story' => 'Our Story', 'core_values' => 'Core Values', 'offers' => 'What We Offer', 'features' => 'Why Choose Enrollzy', 'impacts' => 'Our Impact So Far', 'founders' => 'Meet Our Founders', 'teams' => 'Our Team', 'cta' => 'Call To Action'];
                                $currentOrders = $page->section_orders ?? array_keys($defaultSections);
                                foreach(array_keys($defaultSections) as $key) {
                                    if(!in_array($key, $currentOrders)) {
                                        $currentOrders[] = $key;
                                    }
                                }
                            @endphp
                            
                            <input type="hidden" name="section_orders" id="section_orders_input" value="{{ json_encode($currentOrders) }}">
                            
                            <ul class="list-group mb-4" id="section-order-list">
                                @foreach($currentOrders as $key)
                                    @if(isset($defaultSections[$key]))
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-light border" data-key="{{ $key }}">
                                        <div><i class="fas fa-arrows-alt-v text-muted me-3"></i> <strong class="text-dark">{{ $defaultSections[$key] }}</strong></div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-up" title="Move Up"><i class="fas fa-arrow-up"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-down" title="Move Down"><i class="fas fa-arrow-down"></i></button>
                                        </div>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Save General Content</button>
                            </div>
                        </form>
                    </div>

                    <!-- Offers Tab -->
                    <div class="tab-pane fade" id="offers" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0">What We Offer Cards</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOfferModal">Add New Offer</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Icon</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offers as $offer)
                                    <tr>
                                        <td><img src="{{ asset($offer->icon_image) }}" width="40"></td>
                                        <td>{{ $offer->title }}</td>
                                        <td>{{ $offer->description }}</td>
                                        <td>{{ $offer->sort_order }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editOfferModal{{ $offer->id }}">Edit</button>
                                            <form action="{{ route('admin.about_us.offers.destroy', $offer->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this offer?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @foreach($offers as $offer)
                        <!-- Edit Offer Modal -->
                        <div class="modal fade" id="editOfferModal{{ $offer->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.about_us.offers.update', $offer->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Offer</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $offer->title }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $offer->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Icon Image</label>
                                            <input type="file" name="icon_image" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label>Sort Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $offer->sort_order }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Features Tab -->
                    <div class="tab-pane fade" id="features" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0">Why Choose Enrollzy Features</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFeatureModal">Add New Feature</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Icon</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($features as $feature)
                                    <tr>
                                        <td><img src="{{ asset($feature->icon_image) }}" width="40"></td>
                                        <td>{{ $feature->title }}</td>
                                        <td>{{ $feature->description }}</td>
                                        <td>{{ $feature->sort_order }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editFeatureModal{{ $feature->id }}">Edit</button>
                                            <form action="{{ route('admin.about_us.features.destroy', $feature->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this feature?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @foreach($features as $feature)
                        <!-- Edit Feature Modal -->
                        <div class="modal fade" id="editFeatureModal{{ $feature->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.about_us.features.update', $feature->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Feature</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $feature->title }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $feature->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Icon Image</label>
                                            <input type="file" name="icon_image" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label>Sort Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $feature->sort_order }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Impacts Tab -->
                    <div class="tab-pane fade" id="impacts" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0">Impact Stats</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addImpactModal">Add New Impact</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Icon</th>
                                        <th>Count Text</th>
                                        <th>Label</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($impacts as $impact)
                                    <tr>
                                        <td><img src="{{ asset($impact->icon_image) }}" width="40"></td>
                                        <td>{{ $impact->count_text }}</td>
                                        <td>{{ $impact->label }}</td>
                                        <td>{{ $impact->sort_order }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editImpactModal{{ $impact->id }}">Edit</button>
                                            <form action="{{ route('admin.about_us.impacts.destroy', $impact->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this impact?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @foreach($impacts as $impact)
                        <!-- Edit Impact Modal -->
                        <div class="modal fade" id="editImpactModal{{ $impact->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.about_us.impacts.update', $impact->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Impact</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Count (Number only, "+" will be automatically appended)</label>
                                            <input type="number" name="count_text" class="form-control" value="{{ preg_replace('/[^0-9]/', '', $impact->count_text) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Label</label>
                                            <input type="text" name="label" class="form-control" value="{{ $impact->label }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Icon Image</label>
                                            <input type="file" name="icon_image" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label>Sort Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $impact->sort_order }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Core Values Tab -->
                    <div class="tab-pane fade" id="corevalues" role="tabpanel">
                        <form action="{{ route('admin.about_us.update') }}" method="POST">
                            @csrf
                            <h5 class="fw-bold mb-4">Core Values (Mission, Vision, Philosophy)</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Mission</label>
                                <textarea name="mission_text" class="form-control editor" rows="4">{{ $page->mission_text }}</textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Vision</label>
                                <textarea name="vision_text" class="form-control editor" rows="4">{{ $page->vision_text }}</textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Philosophy</label>
                                <textarea name="philosophy_text" class="form-control editor" rows="4">{{ $page->philosophy_text }}</textarea>
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
                                        <input type="text" name="founder_1_name" class="form-control" value="{{ $page->founder_1_name }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Title (e.g. Co-Founder)</label>
                                        <input type="text" name="founder_1_title" class="form-control" value="{{ $page->founder_1_title }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="founder_1_image" class="form-control">
                                        <small class="text-muted d-block mt-1">Recommended size: 300x300 px (1:1 square aspect ratio)</small>
                                        @if($page->founder_1_image)
                                            <img src="{{ asset($page->founder_1_image) }}" width="100" class="mt-2 rounded">
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Facebook Link</label>
                                        <input type="text" name="founder_1_facebook" class="form-control" value="{{ $page->founder_1_facebook }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">LinkedIn Link</label>
                                        <input type="text" name="founder_1_linkedin" class="form-control" value="{{ $page->founder_1_linkedin }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Twitter Link</label>
                                        <input type="text" name="founder_1_twitter" class="form-control" value="{{ $page->founder_1_twitter }}">
                                    </div>
                                </div>
                                
                                <!-- Founder 2 -->
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-primary mb-3">Founder 2 (Right)</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="founder_2_name" class="form-control" value="{{ $page->founder_2_name }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Title (e.g. Co-Founder)</label>
                                        <input type="text" name="founder_2_title" class="form-control" value="{{ $page->founder_2_title }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="founder_2_image" class="form-control">
                                        <small class="text-muted d-block mt-1">Recommended size: 300x300 px (1:1 square aspect ratio)</small>
                                        @if($page->founder_2_image)
                                            <img src="{{ asset($page->founder_2_image) }}" width="100" class="mt-2 rounded">
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Facebook Link</label>
                                        <input type="text" name="founder_2_facebook" class="form-control" value="{{ $page->founder_2_facebook }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">LinkedIn Link</label>
                                        <input type="text" name="founder_2_linkedin" class="form-control" value="{{ $page->founder_2_linkedin }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Twitter Link</label>
                                        <input type="text" name="founder_2_twitter" class="form-control" value="{{ $page->founder_2_twitter }}">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Common Message (below founders)</label>
                                <textarea name="founders_common_message" class="form-control editor" rows="3">{{ $page->founders_common_message }}</textarea>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Save Founders Content</button>
                            </div>
                        </form>
                    </div>

                    <!-- Teams Content Tab -->
                    <div class="tab-pane fade" id="teams" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0">Team Members</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal"><i class="fas fa-plus me-2"></i> Add Team Member</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Job Profile</th>
                                        <th>Sort Order</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teams as $team)
                                    <tr>
                                        <td>
                                            @if($team->image)
                                                <img src="{{ asset($team->image) }}" width="50" height="50" class="rounded object-fit-cover">
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $team->name }}</td>
                                        <td>{{ $team->job_profile }}</td>
                                        <td>{{ $team->sort_order }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#editTeamModal{{ $team->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.about_us.teams.destroy', $team->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team member?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Team Modal -->
                                    <div class="modal fade" id="editTeamModal{{ $team->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('admin.about_us.teams.update', $team->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Team Member</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label>Name</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $team->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Job Profile</label>
                                                        <input type="text" name="job_profile" class="form-control" value="{{ $team->job_profile }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Image</label>
                                                        <input type="file" name="image" class="form-control">
                                                        @if($team->image)
                                                            <img src="{{ asset($team->image) }}" width="60" class="mt-2 rounded">
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Sort Order</label>
                                                        <input type="number" name="sort_order" class="form-control" value="{{ $team->sort_order }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.about_us.offers.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label>Icon Image</label>
                    <input type="file" name="icon_image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Feature Modal -->
<div class="modal fade" id="addFeatureModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.about_us.features.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label>Icon Image</label>
                    <input type="file" name="icon_image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Impact Modal -->
<div class="modal fade" id="addImpactModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.about_us.impacts.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Impact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Count (Number only, "+" will be automatically appended)</label>
                    <input type="number" name="count_text" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Label</label>
                    <input type="text" name="label" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Icon Image</label>
                    <input type="file" name="icon_image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Team Modal -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.about_us.teams.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Job Profile</label>
                    <input type="text" name="job_profile" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control">
                    <small class="text-muted d-block mt-1">Recommended size: 300x300 px (1:1 square aspect ratio)</small>
                </div>
                <div class="mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        initializeTinyMCE('.editor');

        function updateSectionOrders() {
            let orders = [];
            $('#section-order-list li').each(function() {
                orders.push($(this).data('key'));
            });
            $('#section_orders_input').val(JSON.stringify(orders));
        }

        $('#section-order-list').on('click', '.move-up', function(e) {
            e.preventDefault();
            let li = $(this).closest('li');
            if(li.prev().length > 0) {
                li.insertBefore(li.prev());
                updateSectionOrders();
            }
        });

        $('#section-order-list').on('click', '.move-down', function(e) {
            e.preventDefault();
            let li = $(this).closest('li');
            if(li.next().length > 0) {
                li.insertAfter(li.next());
                updateSectionOrders();
            }
        });
    });
</script>
@endpush
