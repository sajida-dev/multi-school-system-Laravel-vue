import{d as I,s as O,c as g,y as D,b as C,o as i,e as v,f as S,g as t,u as V,h as Q,t as s,j as a,i as l,z as c,F as f,m as b,k as H,_ as T,W as $}from"./app-0hD9jktS.js";import{_ as F}from"./AppLayout.vue_vue_type_script_setup_true_lang-C_OfVMdc.js";import{_ as G}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./RovingFocusGroup-BrsbekXI.js";import"./AvatarImage.vue_vue_type_script_setup_true_lang-DLhJV-ve.js";import"./AppLogo.vue_vue_type_script_setup_true_lang-DV00-4L5.js";import"./building-2-0J7JWYjO.js";import"./users-CQiJNf1m.js";import"./school-SWVnhMs_.js";import"./chevron-down-Bc9-FnA7.js";import"./check-DytFQTDT.js";const R={class:"max-w-4xl mx-auto w-full px-4 py-8 dark:text-neutral-200 text-neutral-900"},W={class:"header"},Y={class:"school-info"},J={class:"flex-1"},U={class:"text-3xl font-bold text-center dark:text-white text-neutral-900 mb-2"},X={class:"flex flex-row gap-5 justify-center"},K={class:"subject-info text-center dark:text-white text-lg text-neutral-700 mb-4"},Z={class:"grade-info text-center text-lg font-semibold dark:text-neutral-200 text-neutral-800"},tt={class:"exam-details flex flex-row justify-between items-center mb-3"},et={class:"text-neutral-700 dark:text-neutral-300"},st={class:"text-neutral-700 dark:text-neutral-300"},ot={class:"instructions"},nt={key:0,class:"list-decimal list-inside space-y-1 text-neutral-700 dark:text-neutral-200"},it=["innerHTML"],at={key:1,class:"list-decimal list-inside space-y-1 text-neutral-700 dark:text-neutral-200"},lt={class:"space-y-8"},rt={key:0,class:"section"},dt={class:"text-neutral-700 mb-6 text-center dark:text-neutral-500"},ut={class:"space-y-6"},pt={class:"question-row"},ct={class:"question-number"},mt={class:"question-text"},xt={class:"question-marks"},ft={key:0,class:"options"},bt={class:"option-label"},gt={key:1,class:"options"},vt={key:1,class:"section"},ht={class:"text-neutral-700 mb-6 text-center dark:text-neutral-500"},_t={class:"space-y-6"},kt={class:"question-row"},yt={class:"question-number"},wt={class:"question-text"},qt={class:"question-marks"},jt={key:2,class:"section"},zt={class:"text-neutral-700 mb-6 text-center dark:text-neutral-500"},Ct={class:"space-y-6"},St={class:"question-row"},Tt={class:"question-number"},At={class:"question-text"},Mt={class:"question-marks"},Nt={class:"footer mt-1 flex justify-between items-center text-neutral-600 text-sm"},Et={class:"flex items-center gap-2"},Pt={key:0,class:"school-logo mb-2"},Bt=["src","alt"],Lt={class:"flex justify-center space-x-4 mt-15 no-print"},It=I({__name:"Show",props:{paper:{}},setup(A){const M=A,N=O(),h=g(()=>x.value.logo_url||"https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s"),d=g(()=>M.paper),x=g(()=>N.props.activeSchool);function u(r){var e;return((e=d.value.questions)==null?void 0:e.filter(p=>p.section===r))||[]}function o(r){return u(r).length}function E(){var r;return((r=d.value.questions)==null?void 0:r.reduce((e,p)=>e+p.marks,0))||0}function P(){$.visit(route("papersquestions.index"))}function B(){const r=window.open("","_blank");if(!r){alert("Please allow popups to print the paper");return}const e=document.querySelector(".max-w-4xl");if(!e)return;const p=`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${d.value.title||"Exam Paper"}</title>
            <style>
                @page {
                    size: A4;
                    margin: 1in;
                }
                
                * {
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Arial body', serif;
                    margin: 0;
                    padding: 0;
                    line-height: 1.6;
                    color: black;
                    background: white;
                    font-size: 12pt;
                }
                
                .paper-container {
                    max-width: 100%;
                    margin: 0 auto;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                
                .header h1 {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                }
                
                .header .subject-info {
                    font-size: 14pt;
                    margin-bottom: 10px;
                }
                
                .header .grade-info {
                    font-size: 14pt;
                    font-weight: bold;
                }
                
                .school-info {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                
                .school-logo {
                    width: 60px;
                    height: 60px;
                    background: #3b82f6;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 18px;
                }
                
                .school-details {
                    text-align: center;
                }
                
                .school-name {
                    font-weight: bold;
                    font-size: 12pt;
                }
                
                .school-address {
                    font-size: 10pt;
                }
                
                .exam-details {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    font-size: 12pt;
                    page-break-after: avoid;
                }
                
                .instructions {
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                
                .instructions h3 {
                    font-size: 14pt;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                
                .instructions ol {
                    margin-left: 20px;
                    font-size: 11pt;
                }
                
                .instructions li {
                    margin-bottom: 5px;
                }
                
                .section {
                    margin-bottom: 40px;
                    page-break-inside: avoid;
                }
                
                .section h2 {
                    text-align: center;
                    text-decoration: underline;
                    margin-bottom: 15px;
                    font-size: 14pt;
                    font-weight: bold;
                    page-break-after: avoid;
                }
                
                .section p {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 11pt;
                    page-break-after: avoid;
                }
                
                .question {
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
                
                .question-header {
                    display: flex;
                    align-items: flex-start;
                    gap: 10px;
                    margin-bottom: 10px;
                }
                
                .question-number {
                    font-weight: bold;
                    min-width: 30px;
                    font-size: 11pt;
                }
                
                .question-text {
                    flex: 1;
                    font-size: 11pt;
                }
                
                .options {
                    margin-left: 20px;
                    margin-top: 10px;
                }
                
                .option {
                    margin-bottom: 5px;
                    font-size: 11pt;
                }
                
                .option-label {
                    font-weight: bold;
                    min-width: 20px;
                    display: inline-block;
                }
                
                .footer {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                    font-size: 10pt;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                    page-break-before: avoid;
                }
                
                hr {
                    border: none;
                    border-top: 1px solid #ccc;
                    margin: 20px 0;
                }
                    .question-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 10px;
                    margin-bottom: 8px;
                    page-break-inside: avoid;
                }

                .question-number {
                    width: 30px;
                    font-weight: bold;
                    font-size: 11pt;
                }

                .question-text {
                    flex: 1;
                    font-size: 11pt;
                }

                .question-marks {
                    font-size: 10pt;
                    font-weight: normal;
                    white-space: nowrap;
                    color: #444;
                }

                .options {
                    margin-left: 40px;
                    margin-top: 6px;
                }

                .option {
                    margin-bottom: 4px;
                    font-size: 10.5pt;
                }

                .option-label {
                    font-weight: bold;
                    margin-right: 4px;
                }               
                @media print {
                    body { 
                         margin: 20; 
                        -webkit-print-color-adjust: exact;
                        color-adjust: exact;
                    }
                    .section { page-break-inside: avoid; }
                    .question { page-break-inside: avoid; }
                    .header { page-break-after: avoid; }
                    .instructions { page-break-after: avoid; }
                    .footer {margin-top: 15px; page-break-before: avoid; }
                    @page {
                        size: A4;
                        margin: 1in;
                    }

                    body {
                        font-family: 'Times New Roman', serif;
                        font-size: 11pt;
                        color: black;
                    }

                    .question-row {
                        page-break-inside: avoid;
                    }

                }
            </style>
        </head>
        <body>
            <div class="paper-container">
                ${e.innerHTML}
            </div>
        </body>
        </html>
    `;r.document.write(p),r.document.close(),r.onload=function(){setTimeout(()=>{r.print(),r.close()},500)}}return(r,e)=>{const p=D("can");return i(),C(F,null,{default:v(()=>{var _,k,y,w,q,j;return[S(V(Q),{title:"View Paper"}),t("div",R,[t("div",W,[t("div",Y,[t("div",J,[t("h1",U,s(((_=d.value.title)==null?void 0:_.toUpperCase())||"PRACTICE EXAMINATION"),1),t("div",X,[t("div",K,s(((k=d.value.subject)==null?void 0:k.name)||"SUBJECT")+" ("+s(((y=d.value.subject)==null?void 0:y.code)||"CODE")+") ",1),t("div",Z," GRADE: "+s(((w=d.value.class)==null?void 0:w.name)||"12"),1)])])]),t("div",tt,[t("div",et,[e[0]||(e[0]=t("strong",null,"Duration:",-1)),a(" "+s(d.value.time_duration||90)+" Minutes ",1)]),t("div",st,[e[1]||(e[1]=t("strong",null,"Maximum Marks:",-1)),a(" "+s(d.value.total_marks||E()),1)])]),t("div",ot,[e[8]||(e[8]=t("h3",{class:"text-lg font-semibold text-neutral-900 mb-3 dark:text-neutral-100"},"General Instructions:",-1)),d.value.instructions?(i(),l("ol",nt,[e[2]||(e[2]=t("li",null,"The Question Paper contains three sections.",-1)),t("li",null,"Section A has "+s(o("objective"))+" questions. Attempt all "+s(Math.min(o("objective"),20))+" questions.",1),t("li",null,"Section B has "+s(o("short_questions"))+" questions. Attempt all "+s(Math.min(o("short_questions"),20))+" questions.",1),t("li",null,"Section C has "+s(o("long_questions"))+" questions. Attempt all "+s(Math.min(o("long_questions"),5))+" questions.",1),e[3]||(e[3]=t("li",null,"All questions carry equal marks.",-1)),e[4]||(e[4]=t("li",null,"There is no negative marking.",-1)),t("li",{innerHTML:d.value.instructions},null,8,it)])):(i(),l("ol",at,[e[5]||(e[5]=t("li",null,"The Question Paper contains three sections.",-1)),t("li",null,"Section A has "+s(o("objective"))+" questions. Attempt all "+s(Math.min(o("objective"),20))+" questions.",1),t("li",null,"Section B has "+s(o("short_questions"))+" questions. Attempt all "+s(Math.min(o("short_questions"),20))+" questions.",1),t("li",null,"Section C has "+s(o("long_questions"))+" questions. Attempt all "+s(Math.min(o("long_questions"),5))+" questions.",1),e[6]||(e[6]=t("li",null,"All questions carry equal marks.",-1)),e[7]||(e[7]=t("li",null,"There is no negative marking.",-1))]))])]),t("div",lt,[u("objective").length>0?(i(),l("div",rt,[e[14]||(e[14]=t("hr",{class:"border-neutral-300 my-6"},null,-1)),e[15]||(e[15]=t("h2",{class:"text-xl font-bold text-center text-neutral-900 mb-4 underline dark:text-neutral-400"}," SECTION A",-1)),t("p",dt,[e[9]||(e[9]=a(" This section consists of ")),t("strong",null,s(o("objective"))+" Multiple Choice Questions",1),e[10]||(e[10]=a(" with overall choice to attempt any ")),t("strong",null,s(Math.min(o("objective"),20))+" questions",1),e[11]||(e[11]=a(". In case more than desirable number of questions are attempted, ")),t("strong",null,"ONLY first "+s(Math.min(o("objective"),20)),1),e[12]||(e[12]=a(" will be considered for evaluation. "))]),t("div",ut,[(i(!0),l(f,null,b(u("objective"),(n,m)=>(i(),l("div",{class:"question",key:n.id},[t("div",pt,[t("div",ct,s(n.question_number||m+1)+".",1),t("div",mt,s(n.text),1),t("div",xt,"("+s(n.marks)+" marks)",1)]),n.type==="multiple_choice"&&n.options?(i(),l("div",ft,[(i(!0),l(f,null,b(n.options,(L,z)=>(i(),l("div",{key:z,class:"option"},[t("span",bt,s(String.fromCharCode(97+z))+")",1),t("span",null,s(L),1)]))),128))])):c("",!0),n.type==="true_false"?(i(),l("div",gt,e[13]||(e[13]=[t("div",{class:"option"},[t("span",{class:"option-label"},"a)"),a(" True")],-1),t("div",{class:"option"},[t("span",{class:"option-label"},"b)"),a(" False")],-1)]))):c("",!0)]))),128))])])):c("",!0),u("short_questions").length>0?(i(),l("div",vt,[e[19]||(e[19]=t("hr",{class:"border-neutral-300 my-6"},null,-1)),e[20]||(e[20]=t("h2",{class:"text-xl font-bold text-center text-neutral-900 mb-4 underline dark:text-neutral-400"}," SECTION B",-1)),t("p",ht,[e[16]||(e[16]=a(" This section consists of ")),t("strong",null,s(o("short_questions"))+" Short Answer Questions",1),e[17]||(e[17]=a(" with overall choice to attempt any ")),t("strong",null,s(Math.min(o("short_questions"),20))+" questions",1),e[18]||(e[18]=a(". "))]),t("div",_t,[(i(!0),l(f,null,b(u("short_questions"),(n,m)=>(i(),l("div",{class:"question",key:n.id},[t("div",kt,[t("div",yt,s(n.question_number||m+1)+".",1),t("div",wt,s(n.text),1),t("div",qt,"("+s(n.marks)+" marks)",1)])]))),128))])])):c("",!0),u("long_questions").length>0?(i(),l("div",jt,[e[24]||(e[24]=t("hr",{class:"border-neutral-300 my-6"},null,-1)),e[25]||(e[25]=t("h2",{class:"text-xl font-bold text-center text-neutral-900 mb-4 underline dark:text-neutral-400"}," SECTION C",-1)),t("p",zt,[e[21]||(e[21]=a(" This section consists of ")),t("strong",null,s(o("long_questions"))+" Long Answer Questions",1),e[22]||(e[22]=a(" with overall choice to attempt any ")),t("strong",null,s(Math.min(o("long_questions"),5))+" questions",1),e[23]||(e[23]=a(". "))]),t("div",Ct,[(i(!0),l(f,null,b(u("long_questions"),(n,m)=>(i(),l("div",{class:"question",key:n.id},[t("div",St,[t("div",Tt,s(n.question_number||m+1)+".",1),t("div",At,s(n.text),1),t("div",Mt,"("+s(n.marks)+" marks)",1)])]))),128))])])):c("",!0)]),e[29]||(e[29]=t("hr",{class:"border-neutral-300 mt-6"},null,-1)),t("div",Nt,[t("div",Et,[h.value?(i(),l("div",Pt,[t("img",{src:h.value,alt:x.value.name,class:"w-8 h-8 rounded-full object-contain"},null,8,Bt)])):c("",!0),t("div",null,s(((q=x.value)==null?void 0:q.name)||"SCHOOL NAME")+", "+s(((j=x.value)==null?void 0:j.address)||"Location"),1)]),e[26]||(e[26]=t("div",null,"Page 1",-1))]),t("div",Lt,[S(T,{variant:"outline",onClick:P},{default:v(()=>e[27]||(e[27]=[a(" Back to Papers ")])),_:1,__:[27]}),H((i(),C(T,{onClick:B},{default:v(()=>e[28]||(e[28]=[a(" Print Paper ")])),_:1,__:[28]})),[[p,"print-papers"]])])])]}),_:1})}}}),Jt=G(It,[["__scopeId","data-v-1c7d7b0f"]]);export{Jt as default};
