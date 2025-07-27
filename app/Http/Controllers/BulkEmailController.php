<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BulkEmailService;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\User;

class BulkEmailController extends Controller
{
    /**
     * Display bulk email interface
     */
    public function index()
    {
        $grades = Grade::all();
        $classrooms = Classroom::with('grade')->get();
        $activeCampaigns = BulkEmailService::getActiveCampaigns();

        return view('admin.bulk-email.index', compact(
            'grades',
            'classrooms',
            'activeCampaigns'
        ));
    }

    /**
     * Send bulk email to all users
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
            'test_mode' => 'boolean'
        ]);

        try {
            $result = BulkEmailService::sendToAllUsers(
                \App\Mail\BulkNotificationMail::class,
                'bulk_notification',
                $request->subject,
                [
                    'message' => $request->message,
                    'sender' => auth()->user()->name
                ],
                [
                    'priority' => $request->priority ?? 'normal',
                    'test_mode' => $request->test_mode ?? false
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار الجماعي بنجاح',
                'campaign_id' => $result['campaign_id'],
                'total_emails' => $result['total_emails']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال الإشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk email to specific roles
     */
    public function sendToRoles(Request $request)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'in:admin,teacher,staff,parent',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
            'test_mode' => 'boolean'
        ]);

        try {
            $result = BulkEmailService::sendToUsersByRole(
                \App\Mail\BulkNotificationMail::class,
                $request->roles,
                'role_notification',
                $request->subject,
                [
                    'message' => $request->message,
                    'sender' => auth()->user()->name,
                    'target_roles' => $request->roles
                ],
                [
                    'priority' => $request->priority ?? 'normal',
                    'test_mode' => $request->test_mode ?? false
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار للأدوار المحددة بنجاح',
                'campaign_id' => $result['campaign_id'],
                'total_emails' => $result['total_emails']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال الإشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk email to parents by grade
     */
    public function sendToGrades(Request $request)
    {
        $request->validate([
            'grade_ids' => 'required|array',
            'grade_ids.*' => 'exists:grades,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
            'test_mode' => 'boolean'
        ]);

        try {
            $result = BulkEmailService::sendToParentsByGrade(
                \App\Mail\BulkNotificationMail::class,
                $request->grade_ids,
                'grade_notification',
                $request->subject,
                [
                    'message' => $request->message,
                    'sender' => auth()->user()->name,
                    'target_grades' => Grade::whereIn('id', $request->grade_ids)->pluck('grade_name')
                ],
                [
                    'priority' => $request->priority ?? 'normal',
                    'test_mode' => $request->test_mode ?? false
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار لأولياء أمور المراحل المحددة بنجاح',
                'campaign_id' => $result['campaign_id'],
                'total_emails' => $result['total_emails']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال الإشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk email to parents by classroom
     */
    public function sendToClassrooms(Request $request)
    {
        $request->validate([
            'classroom_ids' => 'required|array',
            'classroom_ids.*' => 'exists:classrooms,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
            'test_mode' => 'boolean'
        ]);

        try {
            $result = BulkEmailService::sendToStudentsByClassroom(
                \App\Mail\BulkNotificationMail::class,
                $request->classroom_ids,
                'classroom_notification',
                $request->subject,
                [
                    'message' => $request->message,
                    'sender' => auth()->user()->name,
                    'target_classrooms' => Classroom::whereIn('id', $request->classroom_ids)->pluck('full_name')
                ],
                [
                    'priority' => $request->priority ?? 'normal',
                    'test_mode' => $request->test_mode ?? false
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار لأولياء أمور الفصول المحددة بنجاح',
                'campaign_id' => $result['campaign_id'],
                'total_emails' => $result['total_emails']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال الإشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send emergency notification
     */
    public function sendEmergency(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipients' => 'required|in:all,staff,parents'
        ]);

        try {
            $result = BulkEmailService::sendEmergencyNotification(
                $request->subject,
                $request->message,
                $request->recipients
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار الطارئ بنجاح',
                'campaign_id' => $result['campaign_id'],
                'total_emails' => $result['total_emails']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال الإشعار الطارئ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send payment reminders
     */
    public function sendPaymentReminders(Request $request)
    {
        try {
            $result = BulkEmailService::sendPaymentReminders([
                'test_mode' => $request->test_mode ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال تذكيرات المصروفات بنجاح',
                'campaign_id' => $result['campaign_id'],
                'total_emails' => $result['total_emails']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال تذكيرات المصروفات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign status
     */
    public function getCampaignStatus($campaignId)
    {
        $status = BulkEmailService::getCampaignStatus($campaignId);
        return response()->json($status);
    }

    /**
     * Cancel campaign
     */
    public function cancelCampaign($campaignId)
    {
        try {
            $cancelled = BulkEmailService::cancelCampaign($campaignId);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الحملة بنجاح',
                'cancelled_emails' => $cancelled
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إلغاء الحملة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed emails in campaign
     */
    public function retryCampaign($campaignId)
    {
        try {
            $retried = BulkEmailService::retryCampaign($campaignId);

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة إرسال الإيميلات الفاشلة بنجاح',
                'retried_emails' => $retried
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إعادة الإرسال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active campaigns
     */
    public function getActiveCampaigns()
    {
        $campaigns = BulkEmailService::getActiveCampaigns();
        return response()->json($campaigns);
    }
}
