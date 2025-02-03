<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Product;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'طول الشعر المناسب' => ['1', '2', '3', '4'],
            'نوع الشعر' => ['افريقي عنيد', 'افريقي ضعيف', 'المصبوغ', 'ضعيف ولا ارغب في الفرد', 'احتاج ترميم', 'قطني'],
            'عملتي بروتين قبل كدا او اي منتج علاجي' => ['نعم', 'لا'],
            'يوجد حمل او رضاعه' => ['نعم', 'لا'],
            'بتاخدي اي ادويه بانتظام زي ادويه منع الحمل' => ['نعم', 'لا']
        ];
        foreach ($data as $questionText => $answers) {
            $question = Question::create(['name' => $questionText]);
            foreach ($answers as $answerText) {
                $question->answers()->create(['answer' => $answerText]);
            }
        }

        // Assign Answers to Products
        $products = Product::all();
        $answers = Answer::all();

        foreach ($products as $product) {
            // Randomly assign 5 answers to each product
            $product->answers()->attach($answers->random(5));
        }
    }
    
}
