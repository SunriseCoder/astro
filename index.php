<?php
    if (!class_exists('Tr')) { include $_SERVER["DOCUMENT_ROOT"].'/utils/i18n.php'; }
?>
<html>
    <?
        $browser_title = Tr::trs('page.common.browserTitle', 'Astrology - Chaitanya Academy');
        $page_title = 'The Chaitanya Academy ASTRO-PROJECT';

        include $_SERVER["DOCUMENT_ROOT"].'/templates/metadata.php';
    ?>

    <body>
        <table>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_top.php'; ?>
                </td>
            </tr>
            <tr>
                <td class="menu">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/menu.php'; ?>
                </td>
                <td>
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/body_top.php'; ?>

                    <? /* Body Area Start */ ?>

<p>Would you like to participate in an exciting project to shake the foundations of materialism and atheism, and inject spiritual values into public discourse?</p>
<p>If so, the Chaitanya Academy ASTRO-PROJECT is your chance to play a part in bringing about a historic shift in consciousness for a brighter and more harmonious future.</p>
<p>The world-view of secular modernity is that we are purely mechanistic biological automatons who have appeared by chance due to blind molecular collisions.  The materialistic demagogues would have us believe that the entire universe, including ourselves, exists for no ultimate purpose.   This leaves humans with no higher ideal than consumerism and the quest to experience as many “good feels” as we can before the bleak finality and senseless oblivion of death.</p>
<p>At Chaitanya Academy, we actively militate against all such godless ignorance.  To this end, the ASTRO-PROJECT was born.  We want to demonstrate empirically that there is a Divine Intelligence behind the universe by proving the connection between the fate of every living being and the configuration of planets millions of miles away in space.  If Vedic Astrological calculations prove to be statistically and significantly more accurate than random guesses in identifying verifiable incidents and situations in peoples' lives, then the materialistic world view will be threatened with immediate need of revision.</p>
<p>The ASTRO-PROJECT has three stages:</p>
<ol>
    <li>You answer 50 simple multiple-choice questions about your life in the ASTRO-PROJECT SURVEY.  It will only take you five minutes, so please do it now.</li>
    <li>Your date of birth will be sent to one of our expert Vedic astrologers, who will attempt to answer the same 50 questions, WITHOUT SEEING YOUR ANSWERS.</li>
    <li>Our ASTRO-PROJECT computer software will compare your answers with the answers of the astrologers and then calculate their success rate as a percentage.</li>
</ol>

<p>Most of the multiple-choice questions have five options: A B C D E.</p>
<p>So there will be at least a 20% probability of finding the correct answer by random guesswork.  However, if it can be shown that astrological calculations consistently make a much higher rate of accurate prediction, the validity of the Vedic science of astrological calculation will be established.</p>
<p>Once the connection between the destiny of every living being and the stellar constellations has been proven, the entire edifice of the materialistic world-view begins to crumble.</p>
<p>If a person's future was written in the heavens from the moment they were born, then the moment-to-moment decision-making process they experience in real time must be a mistaken perception.  It raises the question of ahankara, physical and psychological agency.  We feel as if we are making choices in the now, when in fact we are simply experiencing the appearance of specific decisions within our consciousness that were already predestined.</p>
<p>Furthermore, the veracity of astrological calculation is tethered to many other aspects of the Vedic paradigm such as karma (predestination based on past action), maya (illusion), dehatma-buddhi (bodily misidentification), reincarnation, the three material modalities, sattva, rajas and tamas, and the indwelling monitor and regulator, the Paramatma.</p>
<p>In conclusion, the Chaitanya Academy ASTRO-PROJECT is designed to challenge the ignorance of modernity and provoke a wave of interest in the basic principles of the Bhagavad Gita and other Vedic literature.</p>
<p>So please take a few minutes to answer the questions in the ASTRO-PROJECT SURVEY here: <a href="questions.php">Survey</a></p>

<p>GUARANTEE OF FULL CONFIDENTIALITY:<br />
Your privacy is important to us.  No one, including the astrologers, will see any of the personal data you give in the survey.  The comparison of your answers and the astrologers' answers will be completely automated by the computer software.  So you can freely answer all the questions in confidence without concern for privacy issues.</p>

                    <? /* Body Area End */ ?>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <? include $_SERVER["DOCUMENT_ROOT"].'/templates/page_footer.php'; ?>
                </td>
            </tr>
        </table>
    </body>
</html>
