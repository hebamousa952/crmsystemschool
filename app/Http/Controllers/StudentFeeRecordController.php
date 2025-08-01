<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentFeeRecord;
use App\Models\Student;
use App\Models\FeePlan;
use App\Models\FeeSetting;
use App\Models\UniformItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StudentFeeRecordController extends Controller
{
    /**
     * Mostrar lista de registros de tarifas de estudiantes
     */
    public function index()
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_INDEX] STARTED ===");
            
            $feeRecords = StudentFeeRecord::with(['student', 'feePlan'])
                ->orderBy('academic_year', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
                
            Log::info("Retrieved " . count($feeRecords) . " fee records");
            
            return view('admin.fees.records.index', compact('feeRecords'));
        } catch (\Exception $e) {
            Log::error("Error in student fee records index: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل سجلات المصروفات: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para crear nuevo registro
     */
    public function create()
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_CREATE] STARTED ===");
            
            $students = Student::orderBy('name')->get();
            $feePlans = FeePlan::active()->orderBy('plan_name')->get();
            $feeSettings = FeeSetting::active()->orderBy('academic_year', 'desc')->get();
            $uniformItems = UniformItem::active()->orderBy('type')->orderBy('name')->get();
            
            return view('admin.fees.records.create', compact('students', 'feePlans', 'feeSettings', 'uniformItems'));
        } catch (\Exception $e) {
            Log::error("Error in student fee records create: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج إنشاء سجل مصروفات: ' . $e->getMessage());
        }
    }
    
    /**
     * Almacenar nuevo registro de tarifas
     */
    public function store(Request $request)
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_STORE] STARTED ===");
            Log::info("Input data: " . json_encode($request->all()));
            
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'academic_year' => 'required|string|max:20',
                'semester' => 'required|string',
                'basic_fees' => 'required|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                Log::error("Validation failed: " . json_encode($validator->errors()->toArray()));
                return back()->withErrors($validator)->withInput();
            }
            
            // Crear nuevo registro de tarifas
            $feeRecord = StudentFeeRecord::create([
                'student_id' => $request->student_id,
                'fee_plan_id' => $request->fee_plan_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                
                // Tarifas
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
                
                // Información de pago
                'is_installment' => $request->has('is_installment'),
                'installments_count' => $request->installments_count ?? 1,
                'down_payment' => $request->down_payment ?? 0,
                'due_date' => $request->due_date ? Carbon::parse($request->due_date) : null,
                
                'notes' => $request->notes,
                'is_active' => true,
                'created_by' => auth()->user()->name ?? 'System',
            ]);
            
            // Calcular totales
            $feeRecord->calculateTotalFees();
            $feeRecord->calculateRemainingAmount();
            $feeRecord->updatePaymentStatus();
            
            // Agregar elementos de uniforme si se seleccionaron
            if ($request->has('uniform_item_ids') && is_array($request->uniform_item_ids)) {
                foreach ($request->uniform_item_ids as $index => $itemId) {
                    if (empty($itemId)) continue;
                    
                    $quantity = $request->uniform_quantities[$index] ?? 1;
                    $price = $request->uniform_prices[$index] ?? null;
                    
                    $feeRecord->addUniformItem($itemId, $quantity, $price);
                }
            }
            
            // Crear cuotas si se seleccionó pago a plazos
            if ($feeRecord->is_installment) {
                // Verificar si hay montos personalizados para las cuotas
                $customAmounts = null;
                if ($request->has('installment_amounts') && is_array($request->installment_amounts)) {
                    $customAmounts = array_filter($request->installment_amounts, function($value) {
                        return $value !== null && $value !== '';
                    });
                    
                    if (count($customAmounts) != $feeRecord->installments_count) {
                        $customAmounts = null;
                    }
                }
                
                // Verificar si hay razones para los montos personalizados
                $customReasons = null;
                if ($request->has('installment_reasons') && is_array($request->installment_reasons)) {
                    $customReasons = $request->installment_reasons;
                }
                
                $startDate = $request->installment_start_date ? Carbon::parse($request->installment_start_date) : null;
                $feeRecord->createInstallments($feeRecord->installments_count, $customAmounts, $startDate, $customReasons);
            }
            
            Log::info("Student fee record created successfully with ID: " . $feeRecord->id);
            
            return redirect()->route('admin.fees.records.show', $feeRecord->id)
                ->with('success', 'تم إنشاء سجل المصروفات بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in student fee records store: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حفظ سجل المصروفات: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Mostrar detalles de un registro específico
     */
    public function show($id)
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_SHOW] STARTED ===");
            Log::info("Showing student fee record ID: " . $id);
            
            $feeRecord = StudentFeeRecord::with([
                'student', 
                'feePlan', 
                'installments', 
                'discounts',
                'studentUniformItems.uniformItem'
            ])->findOrFail($id);
            
            return view('admin.fees.records.show', compact('feeRecord'));
        } catch (\Exception $e) {
            Log::error("Error in student fee records show: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء عرض سجل المصروفات: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para editar registro
     */
    public function edit($id)
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_EDIT] STARTED ===");
            Log::info("Editing student fee record ID: " . $id);
            
            $feeRecord = StudentFeeRecord::with([
                'student', 
                'feePlan', 
                'installments', 
                'studentUniformItems.uniformItem'
            ])->findOrFail($id);
            
            $students = Student::orderBy('name')->get();
            $feePlans = FeePlan::active()->orderBy('plan_name')->get();
            $uniformItems = UniformItem::active()->orderBy('type')->orderBy('name')->get();
            
            return view('admin.fees.records.edit', compact('feeRecord', 'students', 'feePlans', 'uniformItems'));
        } catch (\Exception $e) {
            Log::error("Error in student fee records edit: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج تعديل سجل المصروفات: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar registro de tarifas
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_UPDATE] STARTED ===");
            Log::info("Updating student fee record ID: " . $id);
            Log::info("Input data: " . json_encode($request->all()));
            
            $feeRecord = StudentFeeRecord::findOrFail($id);
            
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'academic_year' => 'required|string|max:20',
                'semester' => 'required|string',
                'basic_fees' => 'required|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                Log::error("Validation failed: " . json_encode($validator->errors()->toArray()));
                return back()->withErrors($validator)->withInput();
            }
            
            // Actualizar registro
            $feeRecord->update([
                'student_id' => $request->student_id,
                'fee_plan_id' => $request->fee_plan_id,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                
                // Tarifas
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
                
                // Información de pago
                'is_installment' => $request->has('is_installment'),
                'installments_count' => $request->installments_count ?? 1,
                'down_payment' => $request->down_payment ?? 0,
                'due_date' => $request->due_date ? Carbon::parse($request->due_date) : null,
                
                'notes' => $request->notes,
                'updated_by' => auth()->user()->name ?? 'System',
            ]);
            
            // Calcular totales
            $feeRecord->calculateTotalFees();
            $feeRecord->calculateRemainingAmount();
            $feeRecord->updatePaymentStatus();
            
            // Actualizar elementos de uniforme
            if ($request->has('update_uniform_items')) {
                // Eliminar elementos existentes
                $feeRecord->studentUniformItems()->delete();
                
                // Agregar nuevos elementos
                if ($request->has('uniform_item_ids') && is_array($request->uniform_item_ids)) {
                    foreach ($request->uniform_item_ids as $index => $itemId) {
                        if (empty($itemId)) continue;
                        
                        $quantity = $request->uniform_quantities[$index] ?? 1;
                        $price = $request->uniform_prices[$index] ?? null;
                        
                        $feeRecord->addUniformItem($itemId, $quantity, $price);
                    }
                }
            }
            
            // Actualizar cuotas si se seleccionó pago a plazos
            if ($request->has('update_installments') && $feeRecord->is_installment) {
                // Verificar si hay montos personalizados para las cuotas
                $customAmounts = null;
                if ($request->has('installment_amounts') && is_array($request->installment_amounts)) {
                    $customAmounts = array_filter($request->installment_amounts, function($value) {
                        return $value !== null && $value !== '';
                    });
                    
                    if (count($customAmounts) != $feeRecord->installments_count) {
                        $customAmounts = null;
                    }
                }
                
                // Verificar si hay razones para los montos personalizados
                $customReasons = null;
                if ($request->has('installment_reasons') && is_array($request->installment_reasons)) {
                    $customReasons = $request->installment_reasons;
                }
                
                $startDate = $request->installment_start_date ? Carbon::parse($request->installment_start_date) : null;
                $feeRecord->createInstallments($feeRecord->installments_count, $customAmounts, $startDate, $customReasons);
            }
            
            Log::info("Student fee record updated successfully");
            
            return redirect()->route('admin.fees.records.show', $feeRecord->id)
                ->with('success', 'تم تحديث سجل المصروفات بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in student fee records update: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث سجل المصروفات: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Eliminar registro de tarifas
     */
    public function destroy($id)
    {
        try {
            Log::info("=== [STUDENT_FEE_RECORDS_DESTROY] STARTED ===");
            Log::info("Deleting student fee record ID: " . $id);
            
            $feeRecord = StudentFeeRecord::findOrFail($id);
            
            // Verificar si hay pagos realizados
            if ($feeRecord->total_paid > 0) {
                Log::warning("Cannot delete fee record: payments have been made");
                return back()->with('error', 'لا يمكن حذف سجل المصروفات لأنه تم إجراء مدفوعات عليه');
            }
            
            // Eliminar registros relacionados
            $feeRecord->installments()->delete();
            $feeRecord->studentUniformItems()->delete();
            $feeRecord->delete();
            
            Log::info("Student fee record deleted successfully");
            
            return redirect()->route('admin.fees.records.index')
                ->with('success', 'تم حذف سجل المصروفات بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in student fee records destroy: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف سجل المصروفات: ' . $e->getMessage());
        }
    }
    
    /**
     * Aplicar configuración de tarifas predeterminada
     */
    public function applyFeeSettings(Request $request, $id)
    {
        try {
            Log::info("=== [APPLY_FEE_SETTINGS] STARTED ===");
            Log::info("Applying fee settings to student fee record ID: " . $id);
            
            $validator = Validator::make($request->all(), [
                'fee_setting_id' => 'required|exists:fee_settings,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'إعدادات الرسوم غير صالحة']);
            }
            
            $feeRecord = StudentFeeRecord::findOrFail($id);
            $feeSetting = FeeSetting::findOrFail($request->fee_setting_id);
            
            $success = $feeSetting->applyToStudentFeeRecord($feeRecord);
            
            if ($success) {
                return response()->json([
                    'success' => true, 
                    'message' => 'تم تطبيق إعدادات الرسوم بنجاح',
                    'feeRecord' => $feeRecord->fresh()
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'فشل تطبيق إعدادات الرسوم']);
            }
        } catch (\Exception $e) {
            Log::error("Error applying fee settings: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Obtener configuración de tarifas para un estudiante
     */
    public function getFeeSettingsForStudent(Request $request)
    {
        try {
            Log::info("=== [GET_FEE_SETTINGS_FOR_STUDENT] STARTED ===");
            
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'academic_year' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'بيانات غير صالحة']);
            }
            
            $student = Student::findOrFail($request->student_id);
            
            $feeSetting = FeeSetting::findSettings(
                $request->academic_year,
                $student->grade_level,
                $student->grade,
                $student->program_type
            );
            
            if (!$feeSetting) {
                return response()->json(['success' => false, 'message' => 'لم يتم العثور على إعدادات رسوم مناسبة']);
            }
            
            return response()->json([
                'success' => true,
                'feeSetting' => $feeSetting,
                'uniformItems' => !empty($feeSetting->default_uniform_items) ? json_decode($feeSetting->default_uniform_items) : []
            ]);
        } catch (\Exception $e) {
            Log::error("Error getting fee settings for student: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Obtener elementos de uniforme para un nivel académico
     */
    public function getUniformItemsForGradeLevel(Request $request)
    {
        try {
            Log::info("=== [GET_UNIFORM_ITEMS_FOR_GRADE_LEVEL] STARTED ===");
            
            $validator = Validator::make($request->all(), [
                'grade_level' => 'required|string',
                'gender' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'بيانات غير صالحة']);
            }
            
            $query = UniformItem::active()->forGradeLevel($request->grade_level);
            
            if ($request->has('gender') && !empty($request->gender)) {
                $query->forGender($request->gender);
            }
            
            $uniformItems = $query->get();
            
            return response()->json([
                'success' => true,
                'uniformItems' => $uniformItems
            ]);
        } catch (\Exception $e) {
            Log::error("Error getting uniform items for grade level: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
}