class Main {
    url="$documents";
    static pdfDoc=null;
    static pageNum=1;
    static numPages=0;
 
    constructor(){
        this.getData(Main.pageNum);
    }
    getData(pageNum){
        pdfjsLib.getDocument(this.url)
            .promise.then(res=>{
               console.log(res);
               Main.pdfDoc=res;
               Main.numPages=Main.pdfDoc.numPages;
               console.log(pageNum);
               Main.renderpage(pageNum);
            });
        
    }  

    static renderPage(num){
        let canvas=document.querySelector("pdfArea");
        let ctx=canvas.getContext('2d');
        let scale=1.5;

        Main.pdfDoc.getPage(num).then(pageResponse=>{
            const viewport=pageResponse.getViewport({scale});
            convas.height=viewport.height;
            convas.width=viewport.width;

            const renderCtx={
                canvasContext:ctx,
                viewport
            }
            pageResponse.render(renderCtx);
        })

        Main.pdfDoc.getPage(num).then(pageResponse =>{
            const viewport=pageResponse.getViewport({scale});
        })
    }
}