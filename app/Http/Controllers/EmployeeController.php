<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function show($id)
    {
        // For now, we'll use static data for RTS004
        // In production, you'd fetch from database: Employee::where('employee_id', $id)->first();
        
        if ($id === 'rts004') {
            $employee = (object) [
                'id' => 'rts004',
                'name' => 'Jannatun Nayem',
                'position' => 'Accounts & Finance Manager',
                'department' => 'Finance & Accounts',
                'company' => 'Rahman Tiles and Sanitary',
                'location' => 'Palong Model Town, Sadar, Shariatpur',
                'email' => 'jannatunnayem@rahmantile.com',
                'phone' => '+880*******',
                'address' => 'Brahmonpara-3526, Cumilla, Bangladesh',
                'start_date' => '2022-07-01',
                'education' => [
                    'degree' => 'Masters of Business Administration (MBA)',
                    'major' => 'Finance and Banking',
                    'cgpa' => '3.81/4.00',
                    'university' => 'Comilla University',
                    'graduation_year' => '2023',
                    
                ],
                'experience_years' => '3+',
                'skills' => [
                    'MS Office Suite (Word, Excel, PowerPoint)',
                    'SPSS, STATA',
                    'Zotero',
                    'Google Forms, Kobo Toolbox',
                    'LaTeX (Basic)',
                    'Statistical Analysis',
                    'Financial Management',
                    'Budget Planning'
                ],
                'languages' => [
                    'English (Fluent Working Proficiency)',
                    'Bengali (Native Language)'
                ],
                'responsibilities' => [
                    'Overseeing daily financial transactions and maintaining accurate accounting records',
                    'Preparing financial statements and periodic financial reports for internal use',
                    'Managing budgets, monitoring cash flows, ensuring compliance with financial regulations',
                    'Coordinating with auditors, banks, and external stakeholders',
                    'Supervising accounts team and financial operations'
                ],
                'achievements' => [
                    'IELTS Score: 6.5 Overall',
                    'President at Finance and Banking Debating Club (2017-2020)',
                    'Member at Finance and Banking Club (2017-2020)',
                    'Member at Blood Donation: BONDO, Comilla University'
                ],
                'about' => 'Ms. Jannatun Nayem has been employed as the Accounts & Finance Manager at Rahman Tiles and Sanitary since July 2022. During her tenure of three years, she has demonstrated exceptional professionalism, dedication, and integrity in all her responsibilities. She joined our organization while still a student and has shown remarkable growth and commitment to her role. Her academic background in Finance and Banking has greatly contributed to the efficient management of our financial operations.',
                'profile_picture' => 'storage/employees/jannatun_nayem.jpg', // Add this line
            ];
            
            return view('employee.show', compact('employee'));
        }
        
        abort(404, 'Employee not found');
    }
}