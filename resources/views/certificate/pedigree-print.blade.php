@php
    // Process pedigree data EXACTLY like Vue component
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
            'children' => $grandparentNodes,  // Use 'children' like Vue component
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

<div class="w-full bg-transparent p-4 print:bg-transparent print:p-2" style="overflow-x: hidden;">
    <div class="flex items-start justify-center" style="transform: scale(0.85); transform-origin: center top;">
        <!-- Level 1 - Root -->
        <div class="relative flex items-center">
            <!-- Root Node Card -->
            <div class="bg-white border-2 border-gray-600 rounded-lg p-2 w-48 h-24 flex items-center space-x-1 print:border-gray-700">
                <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center print:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0 ml-1">
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
                <div class="w-8 h-0.5 bg-gray-600 print:bg-gray-700"></div>

                <!-- Vertical connector for level 2 nodes -->
                @if(count($orgData->children) > 1)
                    <div class="absolute bg-gray-600 print:bg-gray-700 w-0.5" style="left: 224px; height: {{ (count($orgData->children) - 1) * 248 }}px; top: 108px;"></div>
                @endif

                <!-- Level 2 nodes -->
                <div class="ml-0">
                    <div class="flex flex-col space-y-8">
                        @foreach($orgData->children as $level2Node)
                            <div class="relative flex items-center">
                                <!-- Horizontal connector from vertical line to level 2 node -->
                                <div class="w-8 h-0.5 bg-gray-600 print:bg-gray-700"></div>

                                <!-- Level 2 Node Card -->
                                <div class="bg-white border-2 border-gray-600 rounded-lg p-2 w-48 h-24 flex items-center space-x-1 print:border-gray-700">
                                    <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center print:bg-gray-100">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0 ml-1">
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
                                    <div class="w-8 h-0.5 bg-gray-600 print:bg-gray-700"></div>

                                    <!-- Vertical connector for multiple level 3 nodes -->
                                    @if(count($level2Node->children) > 1)
                                        <div class="absolute bg-gray-600 print:bg-gray-700 w-0.5" style="left: 256px; height: {{ (count($level2Node->children) - 1) * 120 }}px; top: 48px;"></div>
                                    @endif

                                    <!-- Level 3 nodes -->
                                    <div class="flex flex-col space-y-6">
                                        @foreach($level2Node->children as $level3Node)
                                            <div class="relative flex items-center">
                                                <!-- Horizontal connector from vertical line to level 3 node -->
                                                <div class="w-8 h-0.5 bg-gray-600 print:bg-gray-700"></div>

                                                <!-- Level 3 Node Card -->
                                                <div class="bg-white border-2 border-gray-600 rounded-lg p-2 w-48 h-24 flex items-center space-x-1 print:border-gray-700">
                                                    <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center print:bg-gray-100">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0 ml-1">
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