<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedigree</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ensure exact Vue component matching */
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .pedigree-container {
            width: 100vw;
            min-height: 100vh;
        }
        
        /* Ensure no line breaks in cards */
        .livestock-card {
            display: flex !important;
            align-items: center !important;
            flex-direction: row !important;
            white-space: nowrap !important;
        }
        
        .livestock-card .flex-1 {
            display: flex !important;
            flex-direction: column !important;
            min-width: 0 !important;
            flex: 1 1 0% !important;
            margin-left: 4px !important;
        }
        
        .livestock-card h3,
        .livestock-card p {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
        }
    </style>
</head>
<body>
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

    <div class="pedigree-container p-8 bg-gray-50">
        <div class="max-w-none mx-auto">
            <div class="flex items-start justify-center overflow-x-auto" style="min-width: 1200px;">
                <!-- Level 1 - Root -->
                <div class="relative flex items-center">
                    <!-- Root Node Card -->
                    <div class="livestock-card bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24" style="display: flex; align-items: center; flex-direction: row; gap: 4px; height: 96px; width: 192px;">
                        <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; min-width: 0; flex: 1 1 0%; margin-left: 4px;">
                            <h3 class="text-xs font-semibold truncate {{ $orgData->isUnknown ? 'text-gray-500' : 'text-gray-900' }}" style="margin: 0; padding: 0; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $orgData->name }}
                            </h3>
                            <p class="text-xs truncate {{ $orgData->isUnknown ? 'text-gray-400' : 'text-gray-600' }}" style="margin: 0; padding: 0; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $orgData->title }}
                            </p>
                        </div>
                    </div>

                    @if($orgData->children && count($orgData->children) > 0)
                        <!-- Horizontal line from root to level 2 -->
                        <div class="w-8 h-px bg-gray-300" style="width: 32px; height: 1px; background-color: #d1d5db;"></div>

                        <!-- Vertical connector for level 2 nodes -->
                        @if(count($orgData->children) > 1)
                            <div class="absolute bg-gray-300 w-px" style="left: 224px; height: {{ (count($orgData->children) - 1) * 248 }}px; top: 108px; background-color: #d1d5db; width: 1px;"></div>
                        @endif

                        <!-- Level 2 nodes -->
                        <div class="ml-0" style="margin-left: 0;">
                            <div class="flex flex-col space-y-8" style="display: flex; flex-direction: column; gap: 32px;">
                                @foreach($orgData->children as $level2Node)
                                    <div class="relative flex items-center" style="position: relative; display: flex; align-items: center;">
                                        <!-- Horizontal connector from vertical line to level 2 node -->
                                        <div class="w-8 h-px bg-gray-300" style="width: 32px; height: 1px; background-color: #d1d5db;"></div>

                                        <!-- Level 2 Node Card -->
                                        <div class="livestock-card bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24" style="display: flex; align-items: center; flex-direction: row; gap: 4px; height: 96px; width: 192px;">
                                            <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; min-width: 0; flex: 1 1 0%; margin-left: 4px;">
                                                <h3 class="text-xs font-semibold truncate {{ $level2Node->isUnknown ? 'text-gray-500' : 'text-gray-900' }}" style="margin: 0; padding: 0; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $level2Node->name }}
                                                </h3>
                                                <p class="text-xs truncate {{ $level2Node->isUnknown ? 'text-gray-400' : 'text-gray-600' }}" style="margin: 0; padding: 0; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $level2Node->title }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($level2Node->children && count($level2Node->children) > 0)
                                            <!-- Horizontal line from level 2 to level 3 -->
                                            <div class="w-8 h-px bg-gray-300" style="width: 32px; height: 1px; background-color: #d1d5db;"></div>

                                            <!-- Vertical connector for multiple level 3 nodes -->
                                            @if(count($level2Node->children) > 1)
                                                <div class="absolute bg-gray-300 w-px" style="left: 256px; height: {{ (count($level2Node->children) - 1) * 120 }}px; top: 48px; background-color: #d1d5db; width: 1px;"></div>
                                            @endif

                                            <!-- Level 3 nodes -->
                                            <div class="flex flex-col space-y-6" style="display: flex; flex-direction: column; gap: 24px;">
                                                @foreach($level2Node->children as $level3Node)
                                                    <div class="relative flex items-center" style="position: relative; display: flex; align-items: center;">
                                                        <!-- Horizontal connector from vertical line to level 3 node -->
                                                        <div class="w-8 h-px bg-gray-300" style="width: 32px; height: 1px; background-color: #d1d5db;"></div>

                                                        <!-- Level 3 Node Card -->
                                                        <div class="livestock-card bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24" style="display: flex; align-items: center; flex-direction: row; gap: 4px; height: 96px; width: 192px;">
                                                            <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1 min-w-0" style="display: flex; flex-direction: column; min-width: 0; flex: 1 1 0%; margin-left: 4px;">
                                                                <h3 class="text-xs font-semibold truncate {{ $level3Node->isUnknown ? 'text-gray-500' : 'text-gray-900' }}" style="margin: 0; padding: 0; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                    {{ $level3Node->name }}
                                                                </h3>
                                                                <p class="text-xs truncate {{ $level3Node->isUnknown ? 'text-gray-400' : 'text-gray-600' }}" style="margin: 0; padding: 0; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
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
</body>
</html>