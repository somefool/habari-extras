var PR_SHOULD_USE_CONTINUATION=true;var PR_TAB_WIDTH=8;var PR_normalizedHtml;var PR;var prettyPrintOne;var prettyPrint;function _pr_isIE6(){var A=navigator&&navigator.userAgent&&/\bMSIE 6\./.test(navigator.userAgent);_pr_isIE6=function(){return A};return A}(function(){function w(Au){Au=Au.split(/ /g);var Av={};for(var At=Au.length;--At>=0;){var As=Au[At];if(As){Av[As]=null}}return Av}var N="break continue do else for if return while ";var d=N+"auto case char const default double enum extern float goto int long register short signed sizeof static struct switch typedef union unsigned void volatile ";var W=d+"catch class delete false import new operator private protected public this throw true try ";var P=W+"alignof align_union asm axiom bool concept concept_map const_cast constexpr decltype dynamic_cast explicit export friend inline late_check mutable namespace nullptr reinterpret_cast static_assert static_cast template typeid typename typeof using virtual wchar_t where ";var g=W+"boolean byte extends final finally implements import instanceof null native package strictfp super synchronized throws transient ";var Ap=g+"as base by checked decimal delegate descending event fixed foreach from group implicit in interface internal into is lock object out override orderby params readonly ref sbyte sealed stackalloc string select uint ulong unchecked unsafe ushort var ";var f=W+"debugger eval export function get null set undefined var with Infinity NaN ";var Y="caller delete die do dump elsif eval exit foreach for goto if import last local my next no our print package redo require sub undef unless until use wantarray while BEGIN END ";var Ab=N+"and as assert class def del elif except exec finally from global import in is lambda nonlocal not or pass print raise try with yield False True None ";var K=N+"alias and begin case class def defined elsif end ensure false in module next nil not or redo rescue retry self super then true undef unless until when yield BEGIN END ";var Aa=N+"case done elif esac eval fi function in local set then until ";var m=(P+Ap+f+Y+Ab+K+Aa);var o="str";var l="kwd";var O="com";var Al="typ";var y="lit";var Ah="pun";var v="pln";var Q="tag";var u="dec";var Ad="src";var Ao="atn";var S="atv";var Ak="nocode";function Aq(As){return(As>="a"&&As<="z")||(As>="A"&&As<="Z")}function n(Av,At,As,Au){Av.unshift(As,Au||0);try{At.splice.apply(At,Av)}finally{Av.splice(0,2)}}var Ai=function(){var Au=["!","!=","!==","#","%","%=","&","&&","&&=","&=","(","*","*=","+=",",","-=","->","/","/=",":","::",";","<","<<","<<=","<=","=","==","===",">",">=",">>",">>=",">>>",">>>=","?","@","[","^","^=","^^","^^=","{","|","|=","||","||=","~","break","case","continue","delete","do","else","finally","instanceof","return","throw","try","typeof"];var Av="(?:(?:(?:^|[^0-9.])\\.{1,3})|(?:(?:^|[^\\+])\\+)|(?:(?:^|[^\\-])-)";for(var As=0;As<Au.length;++As){var At=Au[As];if(Aq(At.charAt(0))){Av+="|\\b"+At}else{Av+="|"+At.replace(/([^=<>:&])/g,"\\$1")}}Av+="|^)\\s*$";return new RegExp(Av)}();var s=/&/g;var z=/</g;var X=/>/g;var k=/\"/g;function e(As){return As.replace(s,"&amp;").replace(z,"&lt;").replace(X,"&gt;").replace(k,"&quot;")}function R(As){return As.replace(s,"&amp;").replace(z,"&lt;").replace(X,"&gt;")}var E=/&lt;/g;var c=/&gt;/g;var D=/&apos;/g;var I=/&quot;/g;var Ar=/&amp;/g;var j=/&nbsp;/g;function T(Av){var Ax=Av.indexOf("&");if(Ax<0){return Av}for(--Ax;(Ax=Av.indexOf("&#",Ax+1))>=0;){var As=Av.indexOf(";",Ax);if(As>=0){var Au=Av.substring(Ax+3,As);var Aw=10;if(Au&&Au.charAt(0)==="x"){Au=Au.substring(1);Aw=16}var At=parseInt(Au,Aw);if(!isNaN(At)){Av=(Av.substring(0,Ax)+String.fromCharCode(At)+Av.substring(As+1))}}}return Av.replace(E,"<").replace(c,">").replace(D,"'").replace(I,'"').replace(Ar,"&").replace(j," ")}function r(As){return"XMP"===As.tagName}function An(Aw,Au){switch(Aw.nodeType){case 1:var At=Aw.tagName.toLowerCase();Au.push("<",At);for(var Av=0;Av<Aw.attributes.length;++Av){var As=Aw.attributes[Av];if(!As.specified){continue}Au.push(" ");An(As,Au)}Au.push(">");for(var Ax=Aw.firstChild;Ax;Ax=Ax.nextSibling){An(Ax,Au)}if(Aw.firstChild||!/^(?:br|link|img)$/.test(At)){Au.push("</",At,">")}break;case 2:Au.push(Aw.name.toLowerCase(),'="',e(Aw.value),'"');break;case 3:case 4:Au.push(R(Aw.nodeValue));break}}var Am=null;function B(Av){if(null===Am){var At=document.createElement("PRE");At.appendChild(document.createTextNode('<!DOCTYPE foo PUBLIC "foo bar">\n<foo />'));Am=!/</.test(At.innerHTML)}if(Am){var Au=Av.innerHTML;if(r(Av)){Au=R(Au)}return Au}var As=[];for(var Aw=Av.firstChild;Aw;Aw=Aw.nextSibling){An(Aw,As)}return As.join("")}function Ag(Au){var As="                ";var At=0;return function(Ay){var Aw=null;var AB=0;for(var Ax=0,AA=Ay.length;Ax<AA;++Ax){var Az=Ay.charAt(Ax);switch(Az){case"\t":if(!Aw){Aw=[]}Aw.push(Ay.substring(AB,Ax));var Av=Au-(At%Au);At+=Av;for(;Av>=0;Av-=As.length){Aw.push(As.substring(0,Av))}AB=Ax+1;break;case"\n":At=0;break;default:++At}}if(!Aw){return Ay}Aw.push(Ay.substring(AB));return Aw.join("")}}var q=/(?:[^<]+|<!--[\s\S]*?-->|<!\[CDATA\[([\s\S]*?)\]\]>|<\/?[a-zA-Z][^>]*>|<)/g;var V=/^<!--/;var t=/^<\[CDATA\[/;var U=/^<br\b/i;var H=/^<(\/?)([a-zA-Z]+)/;function b(AE){var AA=AE.match(q);var AD=[];var Av=0;var As=[];if(AA){for(var Az=0,Au=AA.length;Az<Au;++Az){var AB=AA[Az];if(AB.length>1&&AB.charAt(0)==="<"){if(V.test(AB)){continue}if(t.test(AB)){AD.push(AB.substring(9,AB.length-3));Av+=AB.length-12}else{if(U.test(AB)){AD.push("\n");++Av}else{if(AB.indexOf(Ak)>=0&&Ac(AB)){var At=AB.match(H)[2];var Ay=1;end_tag_loop:for(var Ax=Az+1;Ax<Au;++Ax){var AC=AA[Ax].match(H);if(AC&&AC[2]===At){if(AC[1]==="/"){if(--Ay===0){break end_tag_loop}}else{++Ay}}}if(Ax<Au){As.push(Av,AA.slice(Az,Ax+1).join(""));Az=Ax}else{As.push(Av,AB)}}else{As.push(Av,AB)}}}}else{var Aw=T(AB);AD.push(Aw);Av+=Aw.length}}}return{source:AD.join(""),tags:As}}function Ac(As){return !!As.replace(/\s(\w+)\s*=\s*(?:\"([^\"]*)\"|'([^\']*)'|(\S+))/g,' $1="$2$3$4"').match(/[cC][lL][aA][sS][sS]=\"[^\"]*\bnocode\b/)}function J(Au,At){var As={};(function(){var Ax=Au.concat(At);for(var Ay=Ax.length;--Ay>=0;){var AB=Ax[Ay];var Az=AB[3];if(Az){for(var AA=Az.length;--AA>=0;){As[Az.charAt(AA)]=AB}}}})();var Aw=At.length;var Av=/\S/;return function(Az,AG){AG=AG||0;var AA=[AG,v];var AB="";var AI=0;var AH=Az;while(AH.length){var Ax;var AC=null;var AF;var Ay=As[AH.charAt(0)];if(Ay){AF=AH.match(Ay[1]);AC=AF[0];Ax=Ay[0]}else{for(var AD=0;AD<Aw;++AD){Ay=At[AD];var AE=Ay[2];if(AE&&!AE.test(AB)){continue}AF=AH.match(Ay[1]);if(AF){AC=AF[0];Ax=Ay[0];break}}if(!AC){Ax=v;AC=AH.substring(0,1)}}AA.push(AG+AI,Ax);AI+=AC.length;AH=AH.substring(AC.length);if(Ax!==O&&Av.test(AC)){AB=AC}}return AA}}var A=J([],[[v,/^[^<]+/,null],[u,/^<!\w[^>]*(?:>|$)/,null],[O,/^<!--[\s\S]*?(?:-->|$)/,null],[Ad,/^<\?[\s\S]*?(?:\?>|$)/,null],[Ad,/^<%[\s\S]*?(?:%>|$)/,null],[Ad,/^<(script|style|xmp)\b[^>]*>[\s\S]*?<\/\1\b[^>]*>/i,null],[Q,/^<\/?\w[^<>]*>/,null]]);var Z=/^(<[^>]*>)([\s\S]*)(<\/[^>]*>)$/;function Ae(Ax){var Au=A(Ax);for(var Aw=0;Aw<Au.length;Aw+=2){if(Au[Aw+1]===Ad){var Ay,At;Ay=Au[Aw];At=Aw+2<Au.length?Au[Aw+2]:Ax.length;var As=Ax.substring(Ay,At);var Av=As.match(Z);if(Av){Au.splice(Aw,2,Ay,Q,Ay+Av[1].length,Ad,Ay+Av[1].length+(Av[2]||"").length,Q)}}}return Au}var x=J([[S,/^\'[^\']*(?:\'|$)/,null,"'"],[S,/^\"[^\"]*(?:\"|$)/,null,'"'],[Ah,/^[<>\/=]+/,null,"<>/="]],[[Q,/^[\w:\-]+/,/^</],[S,/^[\w\-]+/,/^=/],[Ao,/^[\w:\-]+/,null],[v,/^\s+/,null," \t\r\n"]]);function i(Ax,At){for(var Av=0;Av<At.length;Av+=2){var Aw=At[Av+1];if(Aw===Q){var Az,As;Az=At[Av];As=Av+2<At.length?At[Av+2]:Ax.length;var Au=Ax.substring(Az,As);var Ay=x(Au,Az);n(Ay,At,Av,2);Av+=Ay.length-2}}return At}function M(Av){var Ax=[],Au=[];if(Av.tripleQuotedStrings){Ax.push([o,/^(?:\'\'\'(?:[^\'\\]|\\[\s\S]|\'{1,2}(?=[^\']))*(?:\'\'\'|$)|\"\"\"(?:[^\"\\]|\\[\s\S]|\"{1,2}(?=[^\"]))*(?:\"\"\"|$)|\'(?:[^\\\']|\\[\s\S])*(?:\'|$)|\"(?:[^\\\"]|\\[\s\S])*(?:\"|$))/,null,"'\""])}else{if(Av.multiLineStrings){Ax.push([o,/^(?:\'(?:[^\\\']|\\[\s\S])*(?:\'|$)|\"(?:[^\\\"]|\\[\s\S])*(?:\"|$)|\`(?:[^\\\`]|\\[\s\S])*(?:\`|$))/,null,"'\"`"])}else{Ax.push([o,/^(?:\'(?:[^\\\'\r\n]|\\.)*(?:\'|$)|\"(?:[^\\\"\r\n]|\\.)*(?:\"|$))/,null,"\"'"])}}Au.push([v,/^(?:[^\'\"\`\/\#]+)/,null," \r\n"]);if(Av.hashComments){Ax.push([O,/^#[^\r\n]*/,null,"#"])}if(Av.cStyleComments){Au.push([O,/^\/\/[^\r\n]*/,null]);Au.push([O,/^\/\*[\s\S]*?(?:\*\/|$)/,null])}if(Av.regexLiterals){var Az=("^/(?=[^/*])(?:[^/\\x5B\\x5C]|\\x5C[\\s\\S]|\\x5B(?:[^\\x5C\\x5D]|\\x5C[\\s\\S])*(?:\\x5D|$))+(?:/|$)");Au.push([o,new RegExp(Az),Ai])}var Aw=w(Av.keywords);Av=null;var At=J(Ax,Au);var Ay=J([],[[v,/^\s+/,null," \r\n"],[v,/^[a-z_$@][a-z_$@0-9]*/i,null],[y,/^0x[a-f0-9]+[a-z]/i,null],[y,/^(?:\d(?:_\d+)*\d*(?:\.\d*)?|\.\d+)(?:e[+\-]?\d+)?[a-z]*/i,null,"123456789"],[Ah,/^[^\s\w\.$@]+/,null]]);function As(AA,AE){for(var AI=0;AI<AE.length;AI+=2){var AB=AE[AI+1];if(AB===v){var AC,AG,AK,AJ;AC=AE[AI];AG=AI+2<AE.length?AE[AI+2]:AA.length;AK=AA.substring(AC,AG);AJ=Ay(AK,AC);for(var AH=0,AD=AJ.length;AH<AD;AH+=2){var AL=AJ[AH+1];if(AL===v){var AN=AJ[AH];var AM=AH+2<AD?AJ[AH+2]:AK.length;var AF=AA.substring(AN,AM);if(AF==="."){AJ[AH+1]=Ah}else{if(AF in Aw){AJ[AH+1]=l}else{if(/^@?[A-Z][A-Z$]*[a-z][A-Za-z$]*$/.test(AF)){AJ[AH+1]=AF.charAt(0)==="@"?y:Al}}}}}n(AJ,AE,AI,2);AI+=AJ.length-2}}return AE}return function(AB){var AA=At(AB);AA=As(AB,AA);return AA}}var Af=M({keywords:m,hashComments:true,cStyleComments:true,multiLineStrings:true,regexLiterals:true});function Aj(As,Aw){for(var Az=0;Az<Aw.length;Az+=2){var At=Aw[Az+1];if(At===Ad){var Au,Ax;Au=Aw[Az];Ax=Az+2<Aw.length?Aw[Az+2]:As.length;var AA=Af(As.substring(Au,Ax));for(var Ay=0,Av=AA.length;Ay<Av;Ay+=2){AA[Ay]+=Au}n(AA,Aw,Az,2);Az+=AA.length-2}}return Aw}function C(As,AA){var AH=false;for(var AE=0;AE<AA.length;AE+=2){var Au=AA[AE+1];var Aw,AC;if(Au===Ao){Aw=AA[AE];AC=AE+2<AA.length?AA[AE+2]:As.length;AH=/^on|^style$/i.test(As.substring(Aw,AC))}else{if(Au===S){if(AH){Aw=AA[AE];AC=AE+2<AA.length?AA[AE+2]:As.length;var AF=As.substring(Aw,AC);var At=AF.length;var Az=(At>=2&&/^[\"\']/.test(AF)&&AF.charAt(0)===AF.charAt(At-1));var Av;var Ax;var AB;if(Az){Ax=Aw+1;AB=AC-1;Av=AF}else{Ax=Aw+1;AB=AC-1;Av=AF.substring(1,AF.length-1)}var AG=Af(Av);for(var AD=0,Ay=AG.length;AD<Ay;AD+=2){AG[AD]+=Ax}if(Az){AG.push(AB,S);n(AG,AA,AE+2,0)}else{n(AG,AA,AE,2)}}AH=false}}}return AA}function L(At){var As=Ae(At);As=i(At,As);As=Aj(At,As);As=C(At,As);return As}function p(AI,At,Ax){var AA=[];var AH=0;var Aw=null;var AB=null;var Au=0;var AG=0;var Ay=Ag(PR_TAB_WIDTH);var Av=/([\r\n ]) /g;var AE=/(^| ) /gm;var Az=/\r\n?|\n/g;var AC=/[ \r\n]$/;var AD=true;function AF(AJ){if(AJ>AH){if(Aw&&Aw!==AB){AA.push("</span>");Aw=null}if(!Aw&&AB){Aw=AB;AA.push('<span class="',Aw,'">')}var AK=R(Ay(AI.substring(AH,AJ))).replace(AD?AE:Av,"$1&nbsp;");AD=AC.test(AK);AA.push(AK.replace(Az,"<br />"));AH=AJ}}while(true){var As;if(Au<At.length){if(AG<Ax.length){As=At[Au]<=Ax[AG]}else{As=true}}else{As=false}if(As){AF(At[Au]);if(Aw){AA.push("</span>");Aw=null}AA.push(At[Au+1]);Au+=2}else{if(AG<Ax.length){AF(Ax[AG]);AB=Ax[AG+1];AG+=2}else{break}}}AF(AI.length);if(Aw){AA.push("</span>")}return AA.join("")}var a={};function G(Au,Av){for(var As=Av.length;--As>=0;){var At=Av[As];if(!a.hasOwnProperty(At)){a[At]=Au}else{if("console" in window){console.log("cannot override language handler %s",At)}}}}G(Af,["default-code"]);G(L,["default-markup","html","htm","xhtml","xml","xsl"]);G(M({keywords:P,hashComments:true,cStyleComments:true}),["c","cc","cpp","cs","cxx","cyc"]);G(M({keywords:g,cStyleComments:true}),["java"]);G(M({keywords:Aa,hashComments:true,multiLineStrings:true}),["bsh","csh","sh"]);G(M({keywords:Ab,hashComments:true,multiLineStrings:true,tripleQuotedStrings:true}),["cv","py"]);G(M({keywords:Y,hashComments:true,multiLineStrings:true,regexLiterals:true}),["perl","pl","pm"]);G(M({keywords:K,hashComments:true,multiLineStrings:true,regexLiterals:true}),["rb"]);G(M({keywords:f,cStyleComments:true,regexLiterals:true}),["js"]);function h(Ax,Av){try{var Ay=b(Ax);var Au=Ay.source;var As=Ay.tags;if(!a.hasOwnProperty(Av)){Av=/^\s*</.test(Au)?"default-markup":"default-code"}var At=a[Av].call({},Au);return p(Au,As,At)}catch(Aw){if("console" in window){console.log(Aw);console.trace()}return Ax}}function F(Ay){var Az=_pr_isIE6();var At=[document.getElementsByTagName("pre"),document.getElementsByTagName("code"),document.getElementsByTagName("xmp")];var Ax=[];for(var Av=0;Av<At.length;++Av){for(var Au=0;Au<At[Av].length;++Au){Ax.push(At[Av][Au])}}At=null;var As=0;function Aw(){var AF=(PR_SHOULD_USE_CONTINUATION?new Date().getTime()+250:Infinity);for(;As<Ax.length&&new Date().getTime()<AF;As++){var AG=Ax[As];if(AG.className&&AG.className.indexOf("prettyprint")>=0){var AC=AG.className.match(/\blang-(\w+)\b/);if(AC){AC=AC[1]}var AL=false;for(var AA=AG.parentNode;AA;AA=AA.parentNode){if((AA.tagName==="pre"||AA.tagName==="code"||AA.tagName==="xmp")&&AA.className&&AA.className.indexOf("prettyprint")>=0){AL=true;break}}if(!AL){var AI=B(AG);AI=AI.replace(/(?:\r\n?|\n)$/,"");var AM=h(AI,AC);if(!r(AG)){AG.innerHTML=AM}else{var AB=document.createElement("PRE");for(var AE=0;AE<AG.attributes.length;++AE){var AN=AG.attributes[AE];if(AN.specified){var AJ=AN.name.toLowerCase();if(AJ==="class"){AB.className=AN.value}else{AB.setAttribute(AN.name,AN.value)}}}AB.innerHTML=AM;AG.parentNode.replaceChild(AB,AG);AG=AB}if(Az&&AG.tagName==="PRE"){var AH=AG.getElementsByTagName("br");for(var AD=AH.length;--AD>=0;){var AK=AH[AD];AK.parentNode.replaceChild(document.createTextNode("\r\n"),AK)}}}}}if(As<Ax.length){setTimeout(Aw,250)}else{if(Ay){Ay()}}}Aw()}window.PR_normalizedHtml=An;window.prettyPrintOne=h;window.prettyPrint=F;window.PR={createSimpleLexer:J,registerLangHandler:G,sourceDecorator:M,PR_ATTRIB_NAME:Ao,PR_ATTRIB_VALUE:S,PR_COMMENT:O,PR_DECLARATION:u,PR_KEYWORD:l,PR_LITERAL:y,PR_NOCODE:Ak,PR_PLAIN:v,PR_PUNCTUATION:Ah,PR_SOURCE:Ad,PR_STRING:o,PR_TAG:Q,PR_TYPE:Al}})();
