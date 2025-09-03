@php
    // Process pedigree data exactly like Vue component
    $currentAnimal = collect($pedigreeData)->firstWhere('depth', 0);
    $parents = collect($pedigreeData)->where('depth', 1)->values();
    $grandparents = collect($pedigreeData)->where('depth', 2)->values();
    
    // Always create 2 parents (even if data doesn't exist) - EXACTLY like Vue
    $parentNodes = collect([0, 1])->map(function($index) use ($parents, $grandparents) {
        $parent = $parents->get($index);
        
        // Always create 2 grandparents per parent (4 total) - EXACTLY like Vue
        $grandparentNodes = collect([0, 1])->map(function($gpIndex) use ($index, $grandparents) {
            $actualGpIndex = $index * 2 + $gpIndex;
            $grandparent = $grandparents->get($actualGpIndex);
            
            return (object)[
                'id' => $grandparent->id ?? "gp-{$actualGpIndex}",
                'name' => $grandparent->name ?? "Kakek/Nenek " . ($actualGpIndex + 1),
                'title' => $grandparent->breed_name ?? 'Unknown',
                'isUnknown' => !$grandparent
            ];
        });
        
        return (object)[
            'id' => $parent->id ?? "parent-{$index}",
            'name' => $parent->name ?? ($index === 0 ? 'Ayah' : 'Ibu'),
            'title' => $parent->breed_name ?? 'Unknown',
            'children' => $grandparentNodes,
            'isUnknown' => !$parent
        ];
    });
    
    $orgData = (object)[
        'id' => $currentAnimal->id ?? 'current',
        'name' => $currentAnimal->name ?? 'Current Animal',
        'title' => $currentAnimal->breed_name ?? 'Unknown',
        'children' => $parentNodes,
        'isUnknown' => !$currentAnimal
    ];
@endphp

<style>
    /* Exact Vue component styles for PDF */
    .vue-pedigree {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .vue-pedigree .bg-gray-50 { background-color: #f9fafb; }
    .vue-pedigree .bg-white { background-color: #ffffff; }
    .vue-pedigree .bg-blue-100 { background-color: #dbeafe; }
    .vue-pedigree .bg-gray-300 { background-color: #d1d5db; }
    .vue-pedigree .border-gray-200 { border-color: #e5e7eb; }
    .vue-pedigree .text-gray-900 { color: #111827; }
    .vue-pedigree .text-gray-600 { color: #4b5563; }
    .vue-pedigree .text-gray-500 { color: #6b7280; }
    .vue-pedigree .text-gray-400 { color: #9ca3af; }
    
    .vue-pedigree .flex { display: flex; }
    .vue-pedigree .items-center { align-items: center; }
    .vue-pedigree .items-start { align-items: flex-start; }
    .vue-pedigree .justify-center { justify-content: center; }
    .vue-pedigree .flex-col { flex-direction: column; }
    .vue-pedigree .relative { position: relative; }
    .vue-pedigree .absolute { position: absolute; }
    .vue-pedigree .flex-shrink-0 { flex-shrink: 0; }
    .vue-pedigree .flex-1 { flex: 1 1 0%; }
    .vue-pedigree .min-w-0 { min-width: 0px; }
    
    .vue-pedigree .p-2 { padding: 8px; }
    .vue-pedigree .p-8 { padding: 32px; }
    .vue-pedigree .mb-8 { margin-bottom: 32px; }
    .vue-pedigree .ml-0 { margin-left: 0; }
    .vue-pedigree .space-x-1 > * + * { margin-left: 4px; }
    .vue-pedigree .space-y-8 > * + * { margin-top: 32px; }
    .vue-pedigree .space-y-6 > * + * { margin-top: 24px; }
    
    .vue-pedigree .w-6 { width: 24px; }
    .vue-pedigree .h-6 { height: 24px; }
    .vue-pedigree .w-8 { width: 32px; }
    .vue-pedigree .h-px { height: 1px; }
    .vue-pedigree .w-48 { width: 192px; }
    .vue-pedigree .h-24 { height: 96px; }
    .vue-pedigree .size-10 { width: 40px; height: 40px; }
    .vue-pedigree .w-px { width: 1px; }
    
    .vue-pedigree .border { border-width: 1px; }
    .vue-pedigree .rounded-lg { border-radius: 8px; }
    .vue-pedigree .rounded-full { border-radius: 50%; }
    .vue-pedigree .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    
    .vue-pedigree .text-xs { font-size: 12px; line-height: 16px; }
    .vue-pedigree .text-2xl { font-size: 24px; line-height: 32px; }
    .vue-pedigree .font-bold { font-weight: 700; }
    .vue-pedigree .font-semibold { font-weight: 600; }
    .vue-pedigree .text-center { text-align: center; }
    .vue-pedigree .truncate { 
        overflow: hidden; 
        text-overflow: ellipsis; 
        white-space: nowrap; 
        max-width: 100%;
    }
    
    /* Ensure text content stays to the right of avatar */
    .vue-pedigree .flex.items-center.space-x-1 {
        display: flex;
        align-items: center;
        flex-direction: row;
    }
    
    .vue-pedigree .flex.items-center.space-x-1 > .flex-1 {
        flex: 1 1 0%;
        min-width: 0px;
        margin-left: 4px;
    }
    
    .vue-pedigree .max-w-6xl { max-width: 1152px; }
    .vue-pedigree .mx-auto { margin-left: auto; margin-right: auto; }
    .vue-pedigree .overflow-x-auto { overflow-x: auto; }
</style>

<div class="vue-pedigree">
    <div class="p-8 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-start justify-center overflow-x-auto">
                <!-- Level 1 - Root -->
                <div class="relative flex items-center">
                    <!-- Root Node Card -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24 flex items-center space-x-1" style="display: flex; align-items: center; flex-direction: row; gap: 4px; height: 96px; width: 192px;">
                        <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; min-width: 0; flex: 1 1 0%;">
                            <h3 class="text-xs font-semibold truncate {{ $orgData->isUnknown ? 'text-gray-500' : 'text-gray-900' }}" style="margin: 0; padding: 0;">
                                {{ $orgData->name }}
                            </h3>
                            <p class="text-xs truncate {{ $orgData->isUnknown ? 'text-gray-400' : 'text-gray-600' }}" style="margin: 0; padding: 0;">
                                {{ $orgData->title }}
                            </p>
                        </div>
                    </div>

                    @if($orgData->children && count($orgData->children) > 0)
                        <!-- Horizontal line from root to level 2 -->
                        <div class="w-8 h-px bg-gray-300"></div>

                        <!-- Vertical connector for level 2 nodes -->
                        @if(count($orgData->children) > 1)
                            <div class="absolute bg-gray-300 w-px" style="left: 224px; height: {{ (count($orgData->children) - 1) * 248 }}px; top: 108px;"></div>
                        @endif

                        <!-- Level 2 nodes -->
                        <div class="ml-0">
                            <div class="flex flex-col space-y-8">
                                @foreach($orgData->children as $level2Node)
                                    <div class="relative flex items-center">
                                        <!-- Horizontal connector from vertical line to level 2 node -->
                                        <div class="w-8 h-px bg-gray-300"></div>

                                        <!-- Level 2 Node Card -->
                                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24 flex items-center space-x-1" style="display: flex; align-items: center; flex-direction: row; gap: 4px; height: 96px; width: 192px;">
                                            <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; min-width: 0; flex: 1 1 0%;">
                                                <h3 class="text-xs font-semibold truncate {{ $level2Node->isUnknown ? 'text-gray-500' : 'text-gray-900' }}" style="margin: 0; padding: 0;">
                                                    {{ $level2Node->name }}
                                                </h3>
                                                <p class="text-xs truncate {{ $level2Node->isUnknown ? 'text-gray-400' : 'text-gray-600' }}" style="margin: 0; padding: 0;">
                                                    {{ $level2Node->title }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($level2Node->children && count($level2Node->children) > 0)
                                            <!-- Horizontal line from level 2 to level 3 -->
                                            <div class="w-8 h-px bg-gray-300"></div>

                                            <!-- Vertical connector for multiple level 3 nodes -->
                                            @if(count($level2Node->children) > 1)
                                                <div class="absolute bg-gray-300 w-px" style="left: 256px; height: {{ (count($level2Node->children) - 1) * 120 }}px; top: 48px;"></div>
                                            @endif

                                            <!-- Level 3 nodes -->
                                            <div class="flex flex-col space-y-6">
                                                @foreach($level2Node->children as $level3Node)
                                                    <div class="relative flex items-center">
                                                        <!-- Horizontal connector from vertical line to level 3 node -->
                                                        <div class="w-8 h-px bg-gray-300"></div>

                                                        <!-- Level 3 Node Card -->
                                                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24 flex items-center space-x-1" style="display: flex; align-items: center; flex-direction: row; gap: 4px; height: 96px; width: 192px;">
                                                            <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center">
                                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; min-width: 0; flex: 1 1 0%;">
                                                                <h3 class="text-xs font-semibold truncate {{ $level3Node->isUnknown ? 'text-gray-500' : 'text-gray-900' }}" style="margin: 0; padding: 0;">
                                                                    {{ $level3Node->name }}
                                                                </h3>
                                                                <p class="text-xs truncate {{ $level3Node->isUnknown ? 'text-gray-400' : 'text-gray-600' }}" style="margin: 0; padding: 0;">
                                                                    {{ $level3Node->title }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>