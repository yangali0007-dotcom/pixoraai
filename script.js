const WA_NUMBER="9152309033"; // Replace with your WhatsApp number, e.g. 919876543210

const navHTML=`<nav><a class="logo" href="index.html">Pixora<span>AI</span></a><button class="menu" id="menu">☰</button><div class="nav-links" id="navLinks"><a href="index.html">Home</a><a href="services.html">Services</a><a href="portfolio.html">Portfolio</a><a href="pricing.html">Pricing</a><a href="contact.html">Contact</a></div></nav>`;
document.getElementById("site-nav").innerHTML=navHTML;
document.getElementById("site-footer").innerHTML=`<footer><div class="logo">Pixora<span>AI</span></div><p>AI-powered marketing visuals for modern brands.</p><p>© 2026 PixoraAI. All rights reserved.</p></footer>`;

const menu=document.getElementById("menu"), links=document.getElementById("navLinks");
if(menu) menu.onclick=()=>links.classList.toggle("open");
const current=location.pathname.split("/").pop()||"index.html";
document.querySelectorAll(".nav-links a").forEach(a=>{if(a.getAttribute("href")===current)a.classList.add("active")});

function setWA(el){if(el){el.href=`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent("Hi PixoraAI! I want to know more about your AI product visuals.")}`;el.target="_blank"}}
setWA(document.getElementById("wa"));setWA(document.getElementById("wa2"));

const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add("show")}),{threshold:.12});
document.querySelectorAll(".reveal").forEach(x=>observer.observe(x));

document.querySelectorAll(".filter").forEach(btn=>btn.addEventListener("click",()=>{
 document.querySelectorAll(".filter").forEach(b=>b.classList.remove("active"));btn.classList.add("active");
 const f=btn.dataset.filter;
 document.querySelectorAll(".portfolio-card").forEach(card=>card.classList.toggle("hidden",f!=="all"&&card.dataset.cat!==f));
}));

const form=document.getElementById("contactForm");
if(form) form.addEventListener("submit",e=>{
 e.preventDefault();
 const data=new FormData(form);
 const msg=`Hi PixoraAI! My name is ${data.get("name")}. Brand: ${data.get("brand")||"N/A"}. Service: ${data.get("service")}. Requirement: ${data.get("message")}`;
 const url=`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`;
 document.getElementById("formStatus").textContent="Opening WhatsApp with your enquiry…";
 window.open(url,"_blank");
});
