<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UniformItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UniformItemController extends Controller
{
    /**
     * Mostrar lista de elementos de uniforme
     */
    public function index()
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_INDEX] STARTED ===");
            
            $uniformItems = UniformItem::orderBy('grade_level')
                ->orderBy('type')
                ->orderBy('name')
                ->get();
                
            Log::info("Retrieved " . count($uniformItems) . " uniform items");
            
            return view('uniforms.index', compact('uniformItems'));
        } catch (\Exception $e) {
            Log::error("Error in uniform items index: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل قائمة الزي المدرسي: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para crear nuevo elemento
     */
    public function create()
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_CREATE] STARTED ===");
            
            return view('uniforms.create');
        } catch (\Exception $e) {
            Log::error("Error in uniform items create: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج إنشاء قطعة زي: ' . $e->getMessage());
        }
    }
    
    /**
     * Almacenar nuevo elemento de uniforme
     */
    public function store(Request $request)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_STORE] STARTED ===");
            Log::info("Input data: " . json_encode($request->all()));
            
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|string',
                'gender' => 'required|string',
                'price' => 'required|numeric|min:0',
                'grade_level' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                Log::error("Validation failed: " . json_encode($validator->errors()->toArray()));
                return back()->withErrors($validator)->withInput();
            }
            
            // Crear nuevo elemento de uniforme
            $uniformItem = UniformItem::create([
                'name' => $request->name,
                'type' => $request->type,
                'gender' => $request->gender,
                'price' => $request->price,
                'grade_level' => $request->grade_level,
                'grade' => $request->grade,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'created_by' => auth()->user()->name ?? 'System',
            ]);
            
            Log::info("Uniform item created successfully with ID: " . $uniformItem->id);
            
            return redirect()->route('uniform-items.index')
                ->with('success', 'تم إنشاء قطعة الزي المدرسي بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in uniform items store: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حفظ قطعة الزي المدرسي: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Mostrar detalles de un elemento específico
     */
    public function show($id)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_SHOW] STARTED ===");
            Log::info("Showing uniform item ID: " . $id);
            
            $uniformItem = UniformItem::findOrFail($id);
            
            return view('uniforms.show', compact('uniformItem'));
        } catch (\Exception $e) {
            Log::error("Error in uniform items show: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء عرض قطعة الزي المدرسي: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario para editar elemento
     */
    public function edit($id)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_EDIT] STARTED ===");
            Log::info("Editing uniform item ID: " . $id);
            
            $uniformItem = UniformItem::findOrFail($id);
            
            return view('uniforms.edit', compact('uniformItem'));
        } catch (\Exception $e) {
            Log::error("Error in uniform items edit: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج تعديل قطعة الزي المدرسي: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar elemento de uniforme
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_UPDATE] STARTED ===");
            Log::info("Updating uniform item ID: " . $id);
            Log::info("Input data: " . json_encode($request->all()));
            
            $uniformItem = UniformItem::findOrFail($id);
            
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|string',
                'gender' => 'required|string',
                'price' => 'required|numeric|min:0',
                'grade_level' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                Log::error("Validation failed: " . json_encode($validator->errors()->toArray()));
                return back()->withErrors($validator)->withInput();
            }
            
            // Actualizar elemento
            $uniformItem->update([
                'name' => $request->name,
                'type' => $request->type,
                'gender' => $request->gender,
                'price' => $request->price,
                'grade_level' => $request->grade_level,
                'grade' => $request->grade,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'updated_by' => auth()->user()->name ?? 'System',
            ]);
            
            Log::info("Uniform item updated successfully");
            
            return redirect()->route('uniform-items.index')
                ->with('success', 'تم تحديث قطعة الزي المدرسي بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in uniform items update: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث قطعة الزي المدرسي: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Eliminar elemento de uniforme
     */
    public function destroy($id)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_DESTROY] STARTED ===");
            Log::info("Deleting uniform item ID: " . $id);
            
            $uniformItem = UniformItem::findOrFail($id);
            
            // Verificar si el elemento está siendo utilizado
            $usageCount = $uniformItem->studentUniformItems()->count();
            
            if ($usageCount > 0) {
                Log::warning("Cannot delete uniform item: it is being used by {$usageCount} students");
                return back()->with('error', 'لا يمكن حذف قطعة الزي المدرسي لأنها مستخدمة من قبل ' . $usageCount . ' طالب');
            }
            
            $uniformItem->delete();
            
            Log::info("Uniform item deleted successfully");
            
            return redirect()->route('uniform-items.index')
                ->with('success', 'تم حذف قطعة الزي المدرسي بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in uniform items destroy: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف قطعة الزي المدرسي: ' . $e->getMessage());
        }
    }
    
    /**
     * Cambiar estado de activación
     */
    public function toggleActive($id)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_TOGGLE_ACTIVE] STARTED ===");
            Log::info("Toggling active status for uniform item ID: " . $id);
            
            $uniformItem = UniformItem::findOrFail($id);
            $newStatus = $uniformItem->toggleActive();
            
            Log::info("Uniform item status toggled to: " . ($newStatus ? 'active' : 'inactive'));
            
            return redirect()->route('uniform-items.index')
                ->with('success', 'تم تغيير حالة قطعة الزي المدرسي بنجاح');
        } catch (\Exception $e) {
            Log::error("Error in uniform items toggle active: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تغيير حالة قطعة الزي المدرسي: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar precio de elemento
     */
    public function updatePrice(Request $request, $id)
    {
        try {
            Log::info("=== [UNIFORM_ITEMS_UPDATE_PRICE] STARTED ===");
            Log::info("Updating price for uniform item ID: " . $id);
            
            $validator = Validator::make($request->all(), [
                'price' => 'required|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'السعر غير صالح']);
            }
            
            $uniformItem = UniformItem::findOrFail($id);
            $success = $uniformItem->updatePrice($request->price);
            
            if ($success) {
                return response()->json(['success' => true, 'message' => 'تم تحديث السعر بنجاح', 'new_price' => $uniformItem->price]);
            } else {
                return response()->json(['success' => false, 'message' => 'فشل تحديث السعر']);
            }
        } catch (\Exception $e) {
            Log::error("Error in uniform items update price: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
}