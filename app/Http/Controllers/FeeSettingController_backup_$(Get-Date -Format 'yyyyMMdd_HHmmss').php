<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeSetting;
use App\Models\UniformItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FeeSettingController extends Controller
{
    /**
     * Mostrar lista de configuraciones de tarifas
     */
    public function index()
    {
        try {
            Log::info("=== [FEE_SETTINGS_INDEX] STARTED ===");
            
            $feeSettings = FeeSetting::orderBy('academic_year', 'desc')
                ->orderBy('grade_level')
                ->orderBy('program_type')
                ->get();
                
            Log::info("Retrieved " . count($feeSettings) . " fee settings");
            
            return view('fees.settings.index', compact('feeSettings'));
        } catch (\Exception $e) {
            Log::error("Error in fee settings index: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل إعدادات الرسوم: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para crear nueva configuración
     */
    public function create()
    {
        try {
            Log::info("=== [FEE_SETTINGS_CREATE] STARTED ===");
            
            // Obtener elementos de uniforme para selección
            $uniformItems = UniformItem::active()->get();
            
            return view('fees.settings.create', compact('uniformItems'));
        } catch (\Exception $e) {
            Log::error("Error in fee settings create: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج إنشاء إعدادات الرسوم: ' . $e->getMessage());
        }
    }
    
    /**
     * Almacenar nueva configuración de tarifas
     */
    public function store(Request $request)
    {
        try {
            Log::info("=== [FEE_SETTINGS_STORE] STARTED ===");
            Log::info("Input data: " . json_encode($request->all()));
            
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'academic_year' => 'required|string|max:20',
                'grade_level' => 'required|string',
                'program_type' => 'required|string',
                'basic_fees' => 'required|numeric|min:0',
                'registration_fees' => 'nullable|numeric|min:0',
                'activities_fees' => 'nullable|numeric|min:0',
                'bus_fees' => 'nullable|numeric|min:0',
                'books_fees' => 'nullable|numeric|min:0',
                'exam_fees' => 'nullable|numeric|min:0',
                'platform_fees' => 'nullable|numeric|min:0',
                'insurance_fees' => 'nullable|numeric|min:0',
                'service_fees' => 'nullable|numeric|min:0',
                'other_fees' => 'nullable|numeric|min:0',
                'max_installments' => 'nullable|integer|min:1',
            ]);
            
            if ($validator->fails()) {
                Log::error("Validation failed: " . json_encode($validator->errors()->toArray()));
                return back()->withErrors($validator)->withInput();
            }
            
            // Procesar datos de descuentos predeterminados
            $defaultDiscounts = [];
            if ($request->has('discount_types') && is_array($request->discount_types)) {
                foreach ($request->discount_types as $index => $type) {
                    if (empty($type)) continue;
                    
                    $value = $request->discount_values[$index] ?? 0;
                    $isPercentage = ($request->discount_is_percentage[$index] ?? 'off') === 'on';
                    
                    $defaultDiscounts[] = [
                        'type' => $type,
                        'value' => $value,
                        'is_percentage' => $isPercentage,
                    ];
                }
            }
            
            // Procesar datos de planes de pago predeterminados
            $defaultInstallmentPlans = [];
            if ($request->has('plan_names') && is_array($request->plan_names)) {
                foreach ($request->plan_names as $index => $name) {
                    if (empty($name)) continue;
                    
                    $count = $request->plan_counts[$index] ?? 1;
                    
                    $defaultInstallmentPlans[] = [
                        'name' => $name,
                        'count' => $count,
                    ];
                }
            }
            
            // Procesar datos de elementos de uniforme predeterminados
            $defaultUniformItems = [];
            if ($request->has('uniform_item_ids') && is_array($request->uniform_item_ids)) {
                foreach ($request->uniform_item_ids as $index => $itemId) {
                    if (empty($itemId)) continue;
                    
                    $quantity = $request->uniform_quantities[$index] ?? 1;
                    $price = $request->uniform_prices[$index] ?? null;
                    
                    $defaultUniformItems[] = [
                        'id' => $itemId,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }
            }
            
            // Crear nueva configuración de tarifas
            $feeSetting = FeeSetting::create([
                'academic_year' => $request->academic_year,
                'grade_level' => $request->grade_level,
                'grade' => $request->grade,
                'program_type' => $request->program_type,
                
                // Tarifas básicas
                'basic_fees' => $request->basic_fees,
                'registration_fees' => $request->registration_fees ?? 0,
                'activities_fees' => $request->activities_fees ?? 0,
                'bus_fees' => $request->bus_fees ?? 0,
                'books_fees' => $request->books_fees ?? 0,
                'exam_fees' => $request->exam_fees ?? 0,
                'platform_fees' => $request->platform_fees ?? 0,
                'insurance_fees' => $request->insurance_fees ?? 0,
                'service_fees' => $request->service_fees ?? 0,
                'other_fees' => $request->other_fees ?? 0,
                'other_fees_description' => $request->other_fees_description,
                
                // Configuraciones adicionales
                'default_discounts' => !empty($defaultDiscounts) ? json_encode($defaultDiscounts) : null,
                'max_installments' => $request->max_installments ?? 10,
                'default_installment_plans' => !empty($defaultInstallmentPlans) ? json_encode($defaultInstallmentPlans) : null,
                'has_uniform' => $request->has('has_uniform'),
                'default_uniform_items' => !empty($defaultUniformItems) ? json_encode($defaultUniformItems) : null,
                
                'is_active' => $request->has('is_active'),
                'notes' => $request->notes,
                'created_by' => auth()->user()->name ?? 'System',
            ]);
            
            Log::info("Fee setting created successfully with ID: " . $feeSetting->id);
            
            return redirect()->route('fee-settings.index')
                ->with('success', 'تم إنشاء إعدادات الرسوم بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in fee settings store: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حفظ إعدادات الرسوم: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Mostrar detalles de una configuración específica
     */
    public function show($id)
    {
        try {
            Log::info("=== [FEE_SETTINGS_SHOW] STARTED ===");
            Log::info("Showing fee setting ID: " . $id);
            
            $feeSetting = FeeSetting::findOrFail($id);
            
            return view('fees.settings.show', compact('feeSetting'));
        } catch (\Exception $e) {
            Log::error("Error in fee settings show: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء عرض إعدادات الرسوم: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para editar configuración
     */
    public function edit($id)
    {
        try {
            Log::info("=== [FEE_SETTINGS_EDIT] STARTED ===");
            Log::info("Editing fee setting ID: " . $id);
            
            $feeSetting = FeeSetting::findOrFail($id);
            $uniformItems = UniformItem::active()->get();
            
            return view('fees.settings.edit', compact('feeSetting', 'uniformItems'));
        } catch (\Exception $e) {
            Log::error("Error in fee settings edit: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج تعديل إعدادات الرسوم: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar configuración de tarifas
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info("=== [FEE_SETTINGS_UPDATE] STARTED ===");
            Log::info("Updating fee setting ID: " . $id);
            Log::info("Input data: " . json_encode($request->all()));
            
            $feeSetting = FeeSetting::findOrFail($id);
            
            // Validar datos de entrada (similar a store)
            $validator = Validator::make($request->all(), [
                'academic_year' => 'required|string|max:20',
                'grade_level' => 'required|string',
                'program_type' => 'required|string',
                'basic_fees' => 'required|numeric|min:0',
                'registration_fees' => 'nullable|numeric|min:0',
                'activities_fees' => 'nullable|numeric|min:0',
                'bus_fees' => 'nullable|numeric|min:0',
                'books_fees' => 'nullable|numeric|min:0',
                'exam_fees' => 'nullable|numeric|min:0',
                'platform_fees' => 'nullable|numeric|min:0',
                'insurance_fees' => 'nullable|numeric|min:0',
                'service_fees' => 'nullable|numeric|min:0',
                'other_fees' => 'nullable|numeric|min:0',
                'max_installments' => 'nullable|integer|min:1',
            ]);
            
            if ($validator->fails()) {
                Log::error("Validation failed: " . json_encode($validator->errors()->toArray()));
                return back()->withErrors($validator)->withInput();
            }
            
            // Procesar datos (similar a store)
            $defaultDiscounts = [];
            if ($request->has('discount_types') && is_array($request->discount_types)) {
                foreach ($request->discount_types as $index => $type) {
                    if (empty($type)) continue;
                    
                    $value = $request->discount_values[$index] ?? 0;
                    $isPercentage = ($request->discount_is_percentage[$index] ?? 'off') === 'on';
                    
                    $defaultDiscounts[] = [
                        'type' => $type,
                        'value' => $value,
                        'is_percentage' => $isPercentage,
                    ];
                }
            }
            
            $defaultInstallmentPlans = [];
            if ($request->has('plan_names') && is_array($request->plan_names)) {
                foreach ($request->plan_names as $index => $name) {
                    if (empty($name)) continue;
                    
                    $count = $request->plan_counts[$index] ?? 1;
                    
                    $defaultInstallmentPlans[] = [
                        'name' => $name,
                        'count' => $count,
                    ];
                }
            }
            
            $defaultUniformItems = [];
            if ($request->has('uniform_item_ids') && is_array($request->uniform_item_ids)) {
                foreach ($request->uniform_item_ids as $index => $itemId) {
                    if (empty($itemId)) continue;
                    
                    $quantity = $request->uniform_quantities[$index] ?? 1;
                    $price = $request->uniform_prices[$index] ?? null;
                    
                    $defaultUniformItems[] = [
                        'id' => $itemId,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }
            }
            
            // Actualizar configuración
            $feeSetting->update([
                'academic_year' => $request->academic_year,
                'grade_level' => $request->grade_level,
                'grade' => $request->grade,
                'program_type' => $request->program_type,
                
                // Tarifas básicas
                'basic_fees' => $request->basic_fees,
                'registration_fees' => $request->registration_fees ?? 0,
                'activities_fees' => $request->activities_fees ?? 0,
                'bus_fees' => $request->bus_fees ?? 0,
                'books_fees' => $request->books_fees ?? 0,
                'exam_fees' => $request->exam_fees ?? 0,
                'platform_fees' => $request->platform_fees ?? 0,
                'insurance_fees' => $request->insurance_fees ?? 0,
                'service_fees' => $request->service_fees ?? 0,
                'other_fees' => $request->other_fees ?? 0,
                'other_fees_description' => $request->other_fees_description,
                
                // Configuraciones adicionales
                'default_discounts' => !empty($defaultDiscounts) ? json_encode($defaultDiscounts) : null,
                'max_installments' => $request->max_installments ?? 10,
                'default_installment_plans' => !empty($defaultInstallmentPlans) ? json_encode($defaultInstallmentPlans) : null,
                'has_uniform' => $request->has('has_uniform'),
                'default_uniform_items' => !empty($defaultUniformItems) ? json_encode($defaultUniformItems) : null,
                
                'is_active' => $request->has('is_active'),
                'notes' => $request->notes,
                'updated_by' => auth()->user()->name ?? 'System',
            ]);
            
            Log::info("Fee setting updated successfully");
            
            return redirect()->route('fee-settings.index')
                ->with('success', 'تم تحديث إعدادات الرسوم بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in fee settings update: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث إعدادات الرسوم: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Eliminar configuración de tarifas
     */
    public function destroy($id)
    {
        try {
            Log::info("=== [FEE_SETTINGS_DESTROY] STARTED ===");
            Log::info("Deleting fee setting ID: " . $id);
            
            $feeSetting = FeeSetting::findOrFail($id);
            $feeSetting->delete();
            
            Log::info("Fee setting deleted successfully");
            
            return redirect()->route('fee-settings.index')
                ->with('success', 'تم حذف إعدادات الرسوم بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in fee settings destroy: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف إعدادات الرسوم: ' . $e->getMessage());
        }
    }
    
    /**
     * Duplicar configuración para un nuevo año académico
     */
    public function duplicate(Request $request, $id)
    {
        try {
            Log::info("=== [FEE_SETTINGS_DUPLICATE] STARTED ===");
            Log::info("Duplicating fee setting ID: " . $id);
            
            $validator = Validator::make($request->all(), [
                'new_academic_year' => 'required|string|max:20',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $feeSetting = FeeSetting::findOrFail($id);
            $newFeeSetting = $feeSetting->duplicateForNewYear($request->new_academic_year);
            
            Log::info("Fee setting duplicated successfully with new ID: " . $newFeeSetting->id);
            
            return redirect()->route('admin.fees.settings.edit', $newFeeSetting->id)
                ->with('success', 'تم نسخ إعدادات الرسوم بنجاح. يمكنك الآن تعديل الإعدادات الجديدة.');
        } catch (\Exception $e) {
            Log::error("Error in fee settings duplicate: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء نسخ إعدادات الرسوم: ' . $e->getMessage());
        }
    }
}